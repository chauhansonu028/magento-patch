<?php

namespace Zendesk\Zendesk\ZendeskApi;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Zendesk\Zendesk\Helper\Config;
use Zendesk\Zendesk\Model\Config\ConfigProvider;

class HttpClient extends \Zendesk\API\HttpClient
{
    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * HttpClient constructor.
     *
     * @param string $subdomain
     * @param Config $configHelper
     * @param LoggerInterface $logger
     * @param string $username
     * @param string $scheme
     * @param string $hostname
     * @param int $port
     * @param Client|null $guzzle
     */
    public function __construct(
        $subdomain,
        // end parent required parameters
        Config $configHelper,
        LoggerInterface $logger,
        // end custom required parameters
        $username = '',
        $scheme = "https",
        $hostname = "zendesk.com",
        $port = 443,
        Client $guzzle = null
    ) {
        parent::__construct($subdomain, $username, $scheme, $hostname, $port, $guzzle);
        $this->configHelper = $configHelper;
        $this->logger = $logger;
    }

    /**
     * If logging enabled, assemble meaningful log data and log to zendesk logger
     *
     * @param string $method
     * @param string $endpoint
     * @param array $requestData
     */
    protected function logRequestData($method, $endpoint, $requestData = [])
    {
        $errorMessage = __('Something went wrong');

        if (!$this->configHelper->getDebugLoggingEnabled()) {
            return; // Nothing to do here.
        }

        $debugData = [
            'method' => $method,
            'url' => $this->getApiUrl() . $this->getApiBasePath() . $endpoint
        ];

        if (!empty($this->getHeaders())) {
            $debugData['headers'] = $this->getHeaders();
        }

        if (!empty($requestData)) {
            $debugData['data'] = json_encode($requestData);
        }

        if ($this->getDebug()->lastResponseError instanceof \Exception) {
            $debugData['error_message'] = $this->getDebug()->lastResponseError->getMessage();
            $errorMessage = $debugData['error_message'];
        }

        $this->logger->debug($errorMessage, $debugData);
    }

    /**
     * @inheritdoc
     */
    public function get($endpoint, $queryParams = [])
    {
        try {
            $return = parent::get($endpoint, $queryParams);

            $this->logRequestData('get', $endpoint, $queryParams);

            return $return;
        } catch (\Zendesk\API\Exceptions\AuthException $ae) {
            $this->logRequestData('get', $endpoint, $queryParams);

            throw $ae;
        } catch (\Zendesk\API\Exceptions\ApiResponseException $are) {
            $this->logRequestData('get', $endpoint, $queryParams);

            throw $are;
        }
    }

    /**
     * @inheritdoc
     */
    public function post($endpoint, $postData = [], $options = [])
    {
        try {
            $return = parent::post($endpoint, $postData, $options);

            $this->logRequestData('post', $endpoint, $postData);

            return $return;
        } catch (\Zendesk\API\Exceptions\AuthException $ae) {
            $this->logRequestData('post', $endpoint, $postData);

            throw $ae;
        } catch (\Zendesk\API\Exceptions\ApiResponseException $are) {
            $this->logRequestData('post', $endpoint, $postData);

            throw $are;
        }
    }

    /**
     * @inheritdoc
     */
    public function put($endpoint, $putData = [])
    {
        try {
            $return = parent::put($endpoint, $putData);

            $this->logRequestData('put', $endpoint, $putData);

            return $return;
        } catch (\Zendesk\API\Exceptions\AuthException $ae) {
            $this->logRequestData('put', $endpoint, $putData);

            throw $ae;
        } catch (\Zendesk\API\Exceptions\ApiResponseException $are) {
            $this->logRequestData('put', $endpoint, $putData);

            throw $are;
        }
    }

    /**
     * @inheritdoc
     */
    public function delete($endpoint)
    {
        try {
            $return = parent::delete($endpoint);

            $this->logRequestData('delete', $endpoint);

            return $return;
        } catch (\Zendesk\API\Exceptions\AuthException $ae) {
            $this->logRequestData('delete', $endpoint);

            throw $ae;
        } catch (\Zendesk\API\Exceptions\ApiResponseException $are) {
            $this->logRequestData('delete', $endpoint);

            throw $are;
        }
    }

    /**
     * Set certain resources to subclass
     *
     * @return array
     */
    public static function getValidSubResources() //phpcs:ignore Magento2.Functions.StaticFunction
    {
        $resources = parent::getValidSubResources();

        $resources['brands'] = \Zendesk\Zendesk\ZendeskApi\Core\Brands::class;
        $resources['apps'] = \Zendesk\Zendesk\ZendeskApi\Core\Apps::class;

        return $resources;
    }
}
