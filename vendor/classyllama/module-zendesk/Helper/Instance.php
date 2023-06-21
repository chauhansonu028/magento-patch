<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2020 Classy Llama Studios, LLC
 */

namespace Zendesk\Zendesk\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Zendesk\API\Exceptions\AuthException;
use Zendesk\Zendesk\Model\Config\ConfigProvider;
use Magento\Framework\App\Helper\Context;
use Zendesk\Zendesk\ZendeskApi\HttpClient;
use Zendesk\Zendesk\ZendeskApi\HttpClientFactory;

class Instance extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var ConfigProvider $configHelper
     */
    protected $configHelper;

    /**
     * @var \Zendesk\API\HttpClient
     */
    protected $zendApiInstance;

    /**
     * @var HttpClientFactory $zendeskClientFactory
     */
    protected $zendeskClientFactory;

    /**
     * @param Context $context
     * @param HttpClientFactory $zendeskClientFactory
     * @param ConfigProvider $configHelper
     */
    public function __construct(
        Context $context,
        HttpClientFactory $zendeskClientFactory, // @phpstan-ignore-line
        ConfigProvider $configHelper
    ) {
        parent::__construct($context);
        $this->configHelper = $configHelper;
        $this->zendeskClientFactory = $zendeskClientFactory;
    }

    /**
     * Get fully configured zend API client instance
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return HttpClient
     * @throws AuthException
     */
    public function getZendeskApiInstance(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        if ($this->zendApiInstance === null) {
            $this->tryValidateIsConfigured($scopeType, $scopeCode);

            /** @var HttpClient $apiClient */
            $this->zendApiInstance = $this->zendeskClientFactory->create(
                ['subdomain' => $this->getSubdomain($scopeType, $scopeCode)]
            );

            $this->zendApiInstance->setAuth(
                'basic',
                [
                    'username' => $this->getUsername($scopeType, $scopeCode),
                    'token' => $this->getToken($scopeType, $scopeCode)
                ]
            );
        }

        return $this->zendApiInstance;
    }

    /**
     * Validate that all required Zendesk config fields are populated,
     * throwing exception if not.
     * See tryAuthenticate to validate that field values are actually valid.
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @throws \InvalidArgumentException
     */
    public function tryValidateIsConfigured($scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        if (empty($this->getSubdomain($scopeType, $scopeCode))) {
            throw new \InvalidArgumentException(__('Zendesk domain not configured.'));
        }
        if (empty($this->getUsername($scopeType, $scopeCode))) {
            throw new \InvalidArgumentException(__('Zendesk agent email not configured.'));
        }
        if (empty($this->getToken($scopeType, $scopeCode))) {
            throw new \InvalidArgumentException(__('Zendesk API token not configured.'));
        }
    }

    /**
     * Get subdomain value
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return mixed
     */
    public function getSubdomain($scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return $this->configHelper->getValue(ConfigProvider::XML_PATH_AGENT_DOMAIN, $scopeType, $scopeCode);
    }

    /**
     * Get username
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return mixed
     */
    public function getUsername($scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return $this->configHelper->getValue(ConfigProvider::XML_PATH_AGENT_EMAIL, $scopeType, $scopeCode);
    }

    /**
     * Get access token
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return mixed
     */
    public function getToken($scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return $this->configHelper->getValue(ConfigProvider::XML_PATH_AGENT_PASSWORD, $scopeType, $scopeCode);
    }
}
