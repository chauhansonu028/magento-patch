<?php

namespace Zendesk\Zendesk\Observer;

use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Zendesk\API\Exceptions\AuthException;
use Zendesk\Zendesk\Helper\Api;
use Zendesk\Zendesk\Helper\Config;
use Zendesk\Zendesk\Helper\Data;
use Zendesk\Zendesk\Helper\Integration;
use Zendesk\Zendesk\Helper\WebWidget;
use Zendesk\Zendesk\Helper\ZendeskApp;

class AutoInstallZendesk implements \Magento\Framework\Event\ObserverInterface
{
    public const AUTO_INSTALL_FLAG_CONFIG_PATH = 'zendesk/zendesk_integration/auto_install';

    /**
     * @var ZendeskApp
     */
    protected $zendeskAppHelper;

    /**
     * @var Api
     */
    protected $apiHelper;

    /**
     * @var WebWidget
     */
    protected $webWidgetHelper;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ManagerInterface
     */
    protected $messageManger;

    /**
     * @var Integration
     */
    protected $integrationHelper;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var Manager
     */
    protected $cacheManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * AutoInstallZendesk constructor.
     * @param Api $apiHelper
     * @param ZendeskApp $zendeskAppHelper
     * @param WebWidget $webWidgetHelper
     * @param Config $configHelper
     * @param Integration $integrationHelper
     * @param StoreRepositoryInterface $storeRepository
     * @param Data $helper
     * @param ManagerInterface $messageManger
     * @param WriterInterface $configWriter
     * @param Manager $cacheManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Api $apiHelper,
        ZendeskApp $zendeskAppHelper,
        WebWidget $webWidgetHelper,
        Config $configHelper,
        Integration $integrationHelper,
        StoreRepositoryInterface $storeRepository,
        Data $helper,
        ManagerInterface $messageManger,
        WriterInterface $configWriter,
        Manager $cacheManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->zendeskAppHelper = $zendeskAppHelper;
        $this->apiHelper = $apiHelper;
        $this->webWidgetHelper = $webWidgetHelper;
        $this->storeRepository = $storeRepository;
        $this->configHelper = $configHelper;
        $this->helper = $helper;
        $this->messageManger = $messageManger;
        $this->integrationHelper = $integrationHelper;
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Install Zendesk app, dealing with any exceptions.
     */
    protected function installZendeskApp()
    {
        try {
            if (!$this->zendeskAppHelper->isZendeskAppInstalled()) {
                $this->zendeskAppHelper->installZendeskApp();
            }
        } catch (\Exception $e) {
            return; // Intentionally swallow exception.
        }
    }

    /**
     * Enable web widget at the global scope.
     */
    protected function enableWebWidget()
    {
        // This action is abstracted into its own method
        // for readability, but there's really only one
        // action currently required.

        $this->webWidgetHelper->toggleWebWidget(true);
    }

    /**
     * If only one store or only one brand, auto-map stores to brands.
     *
     * @throws AuthException
     */
    protected function mapBrands()
    {
        $brands = $this->apiHelper->getZendeskApiInstance()->brands()->getBrands()->brands;
        $stores = array_filter(
            $this->storeRepository->getList(),
            function (\Magento\Store\Api\Data\StoreInterface $store) {
                return $store->getId() != 0;
            }
        );

        if (count($stores) < 2 || count($brands) < 2) {
            // Only one store or only one brand -- map all brands to all stores.

            $storeIds = array_map(function (\Magento\Store\Api\Data\StoreInterface $store) {
                return $store->getId();
            }, $stores);

            foreach ($brands as $brand) {
                $this->configHelper->setBrandStores($brand->id, $storeIds);
            }
        }
    }

    /**
     * @inheritdoc
     *
     * Auto install Zendesk app and enable web widget
     * when API credentials configured.
     */
    public function execute(Observer $observer)
    {
        $website = $observer->getData('website');
        $store = $observer->getData('store');
        $hasChangedPaths = is_array($observer->getData('changed_paths'));
        $changedPaths = $hasChangedPaths ? $observer->getData('changed_paths') : [];

        if ($hasChangedPaths) {
            // If changed paths hint provided, use to determine if API changes have occurred.
            $hasApiCredentialChanges = false;
            foreach (Config::API_CREDENTIAL_PATHS as $credentialPath) {
                if (in_array($credentialPath, $changedPaths)) {
                    $hasApiCredentialChanges = true;
                    break;
                }
            }
        } else {
            // If no config path hint, use global flag to determine if auto-install has ever taken place
            // to ensure auto-install only happens the first time the zendesk section is saved.

            $hasApiCredentialChanges = empty($this->scopeConfig->getValue(self::AUTO_INSTALL_FLAG_CONFIG_PATH));
        }

        // First, several guard clause checks
        if (!$hasApiCredentialChanges) {
            return; // Nothing to do -- bail.
        }

        if (!empty($store) || !empty($website)) {
            return; // This change is intended only to take effect globally -- bail.
        }

        try {
            $this->apiHelper->tryAuthenticate();
        } catch (AuthException $e) {
            return; // Invalid API credentials -- unable to proceed.
        }

        // Making it this far means we should proceed with auto configuration.

        $this->integrationHelper->createIntegration();
        $this->messageManger->addSuccessMessage(__('Magento integration configured.'));

        $this->installZendeskApp();
        $this->messageManger->addSuccessMessage(__('Zendesk app installed.'));

        $this->enableWebWidget();
        $this->messageManger->addSuccessMessage(__('Zendesk web widget enabled.'));

        $this->mapBrands();

        // Set global flag to indicate that auto-install has happened
        $this->configWriter->save(self::AUTO_INSTALL_FLAG_CONFIG_PATH, 1);
        $this->configHelper->setZendeskAppConfigured(true);
        $this->cacheManager->clean([\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER]);
    }
}
