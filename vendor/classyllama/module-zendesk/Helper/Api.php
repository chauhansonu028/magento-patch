<?php

namespace Zendesk\Zendesk\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Zendesk\API\HttpClient;
use Zendesk\Zendesk\ZendeskApi\HttpClientFactory;

class Api extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var HttpClientFactory
     */
    protected $zendeskClientFactory;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var HttpClient
     */
    protected $zendApiInstance;

    /**
     * @var Sunshine $sunshineHelper
     */
    protected $sunshineHelper;

    /**
     * Api constructor.
     *
     * @param Context $context
     * @param Sunshine $sunshineHelper
     * @param HttpClientFactory $zendeskClientFactory
     * @param Config $configHelper
     */
    public function __construct(
        Context $context,
        // End parent parameters
        Sunshine $sunshineHelper,
        HttpClientFactory $zendeskClientFactory, // @phpstan-ignore-line
        \Zendesk\Zendesk\Helper\Config $configHelper
    ) {
        parent::__construct($context);
        $this->zendeskClientFactory = $zendeskClientFactory;
        $this->configHelper = $configHelper;
        $this->sunshineHelper = $sunshineHelper;
    }

    /**
     * Get fully configured zend API client instance
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return \Zendesk\Zendesk\ZendeskApi\HttpClient
     * @throws \Zendesk\API\Exceptions\AuthException
     */
    public function getZendeskApiInstance(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        if ($this->zendApiInstance === null) {
            $this->tryValidateIsConfigured($scopeType, $scopeCode);

            $subdomain = $this->configHelper->getSubDomain($scopeType, $scopeCode);
            $username = $this->configHelper->getAgentEmail($scopeType, $scopeCode);
            $token = $this->configHelper->getApiToken($scopeType, $scopeCode);

            /** @var \Zendesk\Zendesk\ZendeskApi\HttpClient $apiClient */
            $this->zendApiInstance = $this->zendeskClientFactory->create(['subdomain' => $subdomain]);

            $this->zendApiInstance->setAuth('basic', ['username' => $username, 'token' => $token]);
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
     */
    public function tryValidateIsConfigured($scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        $subdomain = $this->configHelper->getSubDomain($scopeType, $scopeCode);
        $username = $this->configHelper->getAgentEmail($scopeType, $scopeCode);
        $token = $this->configHelper->getApiToken($scopeType, $scopeCode);

        if (empty($subdomain)) {
            throw new \InvalidArgumentException(__('Zendesk domain not configured.'));
        }
        if (empty($username)) {
            throw new \InvalidArgumentException(__('Zendesk agent email not configured.'));
        }
        if (empty($token)) {
            throw new \InvalidArgumentException(__('Zendesk API token not configured.'));
        }

        $isZendeskAppConfigured = $this->configHelper->isZendeskAppConfigured($scopeType, $scopeCode);
        if (!$isZendeskAppConfigured) {
            $this->configHelper->setZendeskAppConfigured(true, $scopeType, $scopeCode);
        }
    }

    /**
     * Try to authenticate using auto-configured zend API instance, throwing an AuthException if unable.
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @throws \Zendesk\API\Exceptions\AuthException
     */
    public function tryAuthenticate($scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        try {
            $apiClient = $this->getZendeskApiInstance($scopeType, $scopeCode);

            $me = $apiClient->users()->me();

            if (empty($me->user) || empty($me->user->id)) {
                throw new \Zendesk\API\Exceptions\AuthException(__('Invalid Zendesk API credentials.'));
            }

            // Else, user is authenticated -- nothing to do.
        } catch (\Exception $ex) {
            throw new \Zendesk\API\Exceptions\AuthException(
                __('Invalid Zendesk API credentials.: "%1"', $ex->getMessage())
            );
        }
    }
}
