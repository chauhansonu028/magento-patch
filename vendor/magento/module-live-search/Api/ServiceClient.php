<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearch\Api;

use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\ServicesConnector\Api\ClientResolverInterface;
use Magento\ServicesConnector\Api\KeyValidationInterface;
use Magento\ServicesConnector\Exception\KeyNotFoundException;
use Magento\ServicesConnector\Exception\PrivateKeySignException;
use Magento\ServicesId\Model\ServicesConfigInterface;
use Psr\Log\LoggerInterface;

/**
 * Service client to communicate with SaaS
 */
class ServiceClient implements ServiceClientInterface
{
    /**
     * Config paths
     */
    private const ENVIRONMENT_CONFIG_PATH = 'magento_saas/environment';

    /**
     * Extension name for Services Connector
     */
    private const EXTENSION_NAME = 'Magento_LiveSearch';

    /**
     * @var ClientResolverInterface
     */
    private ClientResolverInterface $clientResolver;

    /**
     * @var KeyValidationInterface
     */
    private KeyValidationInterface $keyValidator;

    /**
     * @var ServicesConfigInterface
     */
    private ServicesConfigInterface $servicesConfig;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $config;

    /**
     * @var Json
     */
    private Json $serializer;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param ClientResolverInterface $clientResolver
     * @param KeyValidationInterface $keyValidator
     * @param ServicesConfigInterface $servicesConfig
     * @param ScopeConfigInterface $config
     * @param Json $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientResolverInterface $clientResolver,
        KeyValidationInterface $keyValidator,
        ServicesConfigInterface $servicesConfig,
        ScopeConfigInterface $config,
        Json $serializer,
        LoggerInterface $logger
    ) {
        $this->clientResolver = $clientResolver;
        $this->keyValidator = $keyValidator;
        $this->servicesConfig = $servicesConfig;
        $this->config = $config;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function request(array $headers, string $path, string $data = ''): array
    {
        $response = '';
        try {
            $client = $this->clientResolver->createHttpClient(
                self::EXTENSION_NAME,
                $this->config->getValue(self::ENVIRONMENT_CONFIG_PATH)
            );
            $environmentId = $this->servicesConfig->getEnvironmentId();
            $headers = \array_merge(
                $headers,
                [
                    'Content-Type' => 'application/json',
                    'Magento-Environment-Id' => $environmentId
                ]
            );

            $options = [
                'headers' => $headers,
                'body' => $data
            ];

            if (!$this->isApiKeyValid()) {
                $this->logger->error(
                    self::EXTENSION_NAME
                    . ': An error occurred trying to contact search backend: API Key Validation failed.'
                );
                throw new KeyInvalidException(__('Magento API Key is invalid'));
            }

            $response = $client->request('POST', $path, $options);
            $result = $this->serializer->unserialize($response->getBody()->getContents());
            $result['status'] = $response->getStatusCode();
            if ($response->getStatusCode() >= 400 || isset($result['errors'])) {
                $this->logger->error(
                    self::EXTENSION_NAME . ': An error occurred in search backend.',
                    ['result' => $result, 'request_id' => $response->getHeader('X-Request-Id')]
                );
                throw new ApiException(__('An error occurred in search backend.'));
            }
            return $result;
        } catch (KeyNotFoundException $e) {
            $this->logger->error(
                self::EXTENSION_NAME . ': Unable to connect to search backend without API key: ' . $e->getMessage()
            );
            throw new KeyInvalidException(__('Magento API Key not found'), $e);
        } catch (InvalidArgumentException $e) {
            // this exception occurs in serializer->unserialize()
            $requestId = '';
            if ($response && !empty($response->getHeader('X-Request-Id'))) {
                $requestId = $response->getHeader('X-Request-Id');
            }
            $this->logger->error(
                self::EXTENSION_NAME . ': An InvalidArgumentException occurred trying to contact search backend. ' .
                'Error: ' . $e->getMessage() . '. Response from backend: ',
                [
                    'response_body' => $response->getBody()->getContents(),
                    'response_status' => $response->getStatusCode(),
                    'request_id' => $requestId
                ]
            );
            throw $e;
        } catch (PrivateKeySignException $e) {
            $this->logger->error(self::EXTENSION_NAME . ': Unable to connect to search backend: ' . $e->getMessage());
            throw new KeyInvalidException(__('Private key signing failed'), $e);
        } catch (GuzzleException $e) {
            $this->logger->error(
                self::EXTENSION_NAME . ': An error occurred trying to contact search backend: ' . $e->getMessage()
            );
            throw new ApiException(__('An error occurred trying to contact search backend.'));
        }
    }

    /**
     * @inheritdoc
     *
     * @return bool
     *
     * @throws KeyNotFoundException
     * @throws PrivateKeySignException
     */
    public function isApiKeyValid(): bool
    {
        return $this->keyValidator->execute(
            self::EXTENSION_NAME,
            $this->config->getValue(self::ENVIRONMENT_CONFIG_PATH)
        );
    }
}
