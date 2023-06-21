<?php

namespace Zendesk\Zendesk\Observer;

use Magento\Framework\Message\ManagerInterface;
use Zendesk\Zendesk\Helper\Api;
use Zendesk\Zendesk\Helper\Config;
use Zendesk\Zendesk\Helper\ZendeskApp;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\Manager;

class UpdateZendeskAppConfig implements \Magento\Framework\Event\ObserverInterface
{
    public const CHANGED_PATH_PATTERN = '#^zendesk\/zendesk_app\/display_[a-z_]+$#';

    /**
     * @var Api
     */
    protected $apiHelper;

    /**
     * @var ZendeskApp
     */
    protected $zendeskAppHelper;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var ManagerInterface
     */
    protected $messageManger;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Manager
     */
    protected $cacheManager;

    /**
     * UpdateZendeskAppConfig constructor.
     * @param ZendeskApp $zendeskAppHelper
     * @param ManagerInterface $messageManger
     * @param RequestInterface $request
     * @param Api $apiHelper
     * @param Config $configHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param Manager $cacheManager
     */
    public function __construct(
        \Zendesk\Zendesk\Helper\ZendeskApp $zendeskAppHelper,
        ManagerInterface $messageManger,
        RequestInterface $request,
        Api $apiHelper,
        Config $configHelper,
        ScopeConfigInterface $scopeConfig,
        Manager $cacheManager
    ) {
        $this->zendeskAppHelper = $zendeskAppHelper;
        $this->messageManger = $messageManger;
        $this->apiHelper = $apiHelper;
        $this->configHelper = $configHelper;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->cacheManager = $cacheManager;
    }

    /**
     * @inheritdoc
     *
     * If Zendesk app config settings are changed, update
     * corresponding config of actual app in Zendesk.
     */
    public function execute(Observer $observer)
    {
        $website = $observer->getData('website');
        $store = $observer->getData('store');
        $hasChangedPaths = is_array($observer->getData('changed_paths'));
        $changedPaths = $hasChangedPaths ? $observer->getData('changed_paths') : [];

        if ($hasChangedPaths) {
            // With changed paths hint, look for specific fields to have been changed.
            $zendeskAppConfigChanged = false;
            foreach ($changedPaths as $changedPath) {
                if (preg_match(self::CHANGED_PATH_PATTERN, $changedPath)) {
                    $zendeskAppConfigChanged = true;
                    break;
                }
            }
        } else {
            // Without changed paths hint, assume app config changes might have happened.
            $zendeskAppConfigChanged = true;
        }

        // Doesn't recognize Brand mapping changes.
        // BEGIN set the values for the Brand mapping.
        $brands = $this->apiHelper->getZendeskApiInstance()->brands()->getBrands()->brands;

        // get values that have been set.
        if (in_array('brand_mapping', array_keys($this->request->getPostValue()['groups']))) {
            // DELETE PREVIOUS MAPPED BRAND IDs BECAUSE OTHERWISE THERE WILL BE STALE ONES THAT WILL MESS UP THE MAPPING
            if (isset($this->scopeConfig->getValue()['zendesk']['brand_mapping'])) {
                $existingBrandConfig = $this->scopeConfig->getValue()['zendesk']['brand_mapping'];
                foreach (array_keys($existingBrandConfig) as $brandConfig) {
                    // get only number
                    $valueArray = explode('-', $brandConfig);
                    $brandId = end($valueArray);
                    $this->configHelper->deleteBrandStores($brandId);
                }
            }

            $brandsConfig = $this->request->getPostValue()['groups']['brand_mapping']['fields'];

            foreach ($brands as $brand) {
                $brandPath = 'brand-mapping-' . $brand->id;
                $newBrandValue = [];

                if (in_array($brandPath, array_keys($brandsConfig))) {
                    $newBrandValue =  $brandsConfig[$brandPath]['value'];
                }

                $this->configHelper->setBrandStores($brand->id, $newBrandValue);
            }
        }
        // Newly set config value won't take effect unless config cache is cleaned.
        $this->cacheManager->clean([\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER]);
        // END set values for brand mapping.

        // Clear saved widget snippets so that any updates will take effect properly.
        $this->configHelper->deleteWidgetSnippets();

        if (!$zendeskAppConfigChanged) {
            return; // nothing to do here.
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

        try {
            $this->zendeskAppHelper->updateZendeskAppConfiguration($scopeType, $scopeId);
        } catch (\Exception $e) {
            $this->messageManger->addErrorMessage(
                __(
                    'Zendesk app changes detected, but unable to actually ' .
                    'update app configuration in Zendesk account. Error message: "%1".',
                    $e->getMessage()
                )
            );
        }
    }
}
