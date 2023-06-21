<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SaaSCommon\Model\Http\Command;

use GuzzleHttp\Client;
use Laminas\Http\Request;
use Magento\SaaSCommon\Model\DataFilter;
use Psr\Http\Message\ResponseInterface;
use Magento\SaaSCommon\Model\Exception\UnableSendData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\SaaSCommon\Model\Http\Converter\Factory;
use Magento\SaaSCommon\Model\Http\ConverterInterface;
use Magento\ServicesConnector\Api\ClientResolverInterface;
use Magento\ServicesId\Model\ServicesConfigInterface;
use Magento\SaaSCommon\Model\Logging\SaaSExportLoggerInterface as LoggerInterface;

/**
 * Class responsible for call execution to SaaS Service
 */
class SubmitFeed
{
    /**
     * Config paths
     */
    private const ROUTE_CONFIG_PATH = 'magento_saas/routes/';
    private const ENVIRONMENT_CONFIG_PATH = 'magento_saas/environment';

    /**
     * Extension name for Services Connector
     */
    private const EXTENSION_NAME = 'Magento_DataExporter';

    /**
     * @var ClientResolverInterface
     */
    private $clientResolver;

    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var ServicesConfigInterface
     */
    private $servicesConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $extendedLog;

    /**
     * @var string[]
     */
    private $headers;

    /**
     * @var DataFilter
     */
    private $dataFilter;

    /**
     * @param ClientResolverInterface $clientResolver
     * @param Factory $converterFactory
     * @param ScopeConfigInterface $config
     * @param ServicesConfigInterface $servicesConfig
     * @param LoggerInterface $logger
     * @param DataFilter $dataFilter
     * @param bool $extendedLog
     * @param string[] $headers
     */
    public function __construct(
        ClientResolverInterface $clientResolver,
        Factory $converterFactory,
        ScopeConfigInterface $config,
        ServicesConfigInterface $servicesConfig,
        LoggerInterface $logger,
        DataFilter $dataFilter,
        bool $extendedLog = false,
        array $headers = []
    ) {
        $this->clientResolver = $clientResolver;
        $this->converter = $converterFactory->create();
        $this->config = $config;
        $this->servicesConfig = $servicesConfig;
        $this->logger = $logger;
        $this->extendedLog = $extendedLog;
        $this->headers = $headers;
        $this->dataFilter = $dataFilter;
    }

    /**
     * Build URL to SaaS Service
     *
     * @param string $feedName
     * @return string
     */
    private function getUrl(string $feedName) : string
    {
        $route = '/' . $this->config->getValue(self::ROUTE_CONFIG_PATH . $feedName) . '/';
        $environmentId = $this->servicesConfig->getEnvironmentId();
        return $route . $environmentId;
    }

    /**
     * Execute call to SaaS Service
     *
     * @param string $feedName
     * @param array $data
     * @param int|null $timeout
     * @return bool
     * @throws UnableSendData
     */
    public function execute(string $feedName, array $data, int $timeout = null) : bool
    {
        try {
            $client = $this->clientResolver->createHttpClient(
                self::EXTENSION_NAME,
                $this->config->getValue(self::ENVIRONMENT_CONFIG_PATH)
            );
            $headers = $this->getHeaders();
            if (null !== $this->converter->getContentEncoding()) {
                $headers['Content-Encoding'] = $this->converter->getContentEncoding();
            }

            $data = $this->dataFilter->filter($feedName, $data);
            if ($this->extendedLog) {
                $debugData = "feed=<$feedName>\n";
                $debugData .= json_encode($data, JSON_PRETTY_PRINT);
                $this->logger->info($debugData);
            }
            $body = $this->converter->toBody($data);
            $options = [
                'headers' => $headers,
                'body' => $body
            ];

            if (null !== $timeout) {
                $options['timeout'] = $timeout;
            }

            if ($this->servicesConfig->isApiKeySet()) {
                $response = $client->request(Request::METHOD_POST, $this->getUrl($feedName), $options);
                $result = !($response->getStatusCode() >= 500);
                if ($response->getStatusCode() !== 200) {
                    $this->logger->error(
                        'Export error. API request was not successful.',
                        $this->prepareLog($client, $response, $feedName, $data)
                    );
                }
            } else {
                $this->logger->error('API Keys Validation Failed');
                throw new UnableSendData('Unable to send data to service');
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
            throw new UnableSendData('Unable to send data to service');
        }

        return $result;
    }

    /**
     * Prepare log formatting.
     *
     * @param Client $client
     * @param ResponseInterface $response
     * @param string $feedName
     * @param array $payload
     * @return array
     */
    private function prepareLog(Client $client, ResponseInterface $response, string $feedName, array $payload): array
    {
        $clientConfig = $client->getConfig();

        $log = [
            'status_code' => $response->getStatusCode(),
            'reason' => $response->getReasonPhrase(),
            'url' => $this->getUrl($feedName),
            'base_uri'=> $clientConfig['base_uri']
                ? $clientConfig['base_uri']->__toString() : 'base uri wasn\'t set',
            'response' => $response->getBody()->getContents()
        ];

        if (true === $this->extendedLog) {
            $log['headers'] = $clientConfig['headers'] ?? 'no headers';
            $log['payload'] = $payload;
        }
        return $log;
    }

    /**
     * Create a list of headers for the feed submit request.
     *
     * @return array
     */
    private function getHeaders(): array
    {
        $headers = [
            'Content-Type' => $this->converter->getContentMediaType(),
        ];

        if (empty($this->headers)) {
            return $headers;
        }

        foreach ($this->headers as $headerName => $headerValue) {
            if (!empty($headerValue)) {
                $headers[$headerName] = $headerValue;
            }
        }

        return $headers;
    }
}
