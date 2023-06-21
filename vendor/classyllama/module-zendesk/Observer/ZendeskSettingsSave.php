<?php

namespace Zendesk\Zendesk\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Zendesk\API\Exceptions\AuthException;
use Zendesk\Zendesk\Helper\Api;
use Zendesk\Zendesk\Helper\Config;

class ZendeskSettingsSave implements \Magento\Framework\Event\ObserverInterface
{
    public const ZENDESK_CONFIG_SECTION_NAME = 'zendesk';

    /**
     * @var Api
     */
    protected $apiHelper;
    /**
     * @var ManagerInterface
     */
    protected $messageManager;
    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * ZendeskSettingsSave constructor.
     * @param Config $configHelper
     * @param Api $apiHelper
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Config $configHelper,
        Api $apiHelper,
        ManagerInterface $messageManager
    ) {
        $this->apiHelper = $apiHelper;
        $this->messageManager = $messageManager;
        $this->configHelper = $configHelper;
    }

    /**
     * @inheritdoc
     *
     * If saving Zendesk section and authentication values have been provided,
     * validate and show error message if they are invalid.
     */
    public function execute(Observer $observer)
    {
        $website = $observer->getData('website');
        $store = $observer->getData('store');
        $hasChangedPaths = is_array($observer->getData('changed_paths'));
        $changedPaths = $hasChangedPaths ? $observer->getData('changed_paths') : [];

        if ($hasChangedPaths) {
            // With changed paths hint, look for specific fields to have been changed.
            $hasApiCredentialChanges = false;
            foreach (Config::API_CREDENTIAL_PATHS as $apiCredentialPath) {
                if (in_array($apiCredentialPath, $changedPaths)) {
                    $hasApiCredentialChanges = true;
                    break;
                }
            }
        } else {
            // Without changed paths hint, assume API credential changes might have happened.
            $hasApiCredentialChanges = true;
        }

        if (!$hasApiCredentialChanges) {
            return; // No API credential config changes -- bail.
        }

        // Determine scope type and code from presence or absence of $website or $store
        $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $scopeId = 0;

        if (!empty($website)) {
            $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
            $scopeId = $website;
        }
        if (!empty($store)) {
            $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $scopeId = $store;
        }

        if (empty($this->configHelper->getDomain($scopeType, $scopeId))) {
            return;
        }
        if (empty($this->configHelper->getAgentEmail($scopeType, $scopeId))) {
            return;
        }
        if (empty($this->configHelper->getApiToken($scopeType, $scopeId))) {
            return;
        }

        try {
            $this->apiHelper->tryAuthenticate($scopeType, $scopeId);
            $this->configHelper->setZendeskAppConfigured(true, $scopeType, $scopeId);
        } catch (AuthException $e) {
            $this->messageManager->addErrorMessage(__('Unable to authenticate Zendesk credentials.'));
        }
    }
}
