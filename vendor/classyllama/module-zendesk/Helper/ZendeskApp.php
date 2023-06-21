<?php

namespace Zendesk\Zendesk\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use stdClass;
use Zendesk\API\Exceptions\AuthException;

class ZendeskApp extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Api
     */
    protected $apiHelper;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var Integration
     */
    protected $integrationHelper;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var GroupRepositoryInterface
     */
    protected $storeGroupRepository;

    /**
     * @var stdClass|null
     */
    protected $zendeskAppInstance = null;

    /**
     * ZendeskApp constructor.
     *
     * @param Context $context
     * @param Api $apiHelper
     * @param Config $configHelper
     * @param Integration $integrationHelper
     * @param StoreRepositoryInterface $storeRepository
     * @param GroupRepositoryInterface $storeGroupRepository
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        // End parent parameters
        \Zendesk\Zendesk\Helper\Api $apiHelper,
        \Zendesk\Zendesk\Helper\Config $configHelper,
        \Zendesk\Zendesk\Helper\Integration $integrationHelper,
        StoreRepositoryInterface $storeRepository,
        GroupRepositoryInterface $storeGroupRepository,
        WebsiteRepositoryInterface $websiteRepository,
        \Zendesk\Zendesk\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->apiHelper = $apiHelper;
        $this->configHelper = $configHelper;
        $this->integrationHelper = $integrationHelper;
        $this->storeRepository = $storeRepository;
        $this->websiteRepository = $websiteRepository;
        $this->helper = $helper;
        $this->storeGroupRepository = $storeGroupRepository;
    }

    /**
     * Get Zendesk app instance or `null` if not installed.
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return stdClass|null
     * @throws AuthException
     */
    protected function getZendeskAppInstance($scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        if ($this->zendeskAppInstance === null) {
            $appId = $this->configHelper->getZendeskAppId($scopeType, $scopeCode);

            $api = $this->apiHelper->getZendeskApiInstance($scopeType, $scopeCode);

            $installedApps = $api->apps()->getInstalledApps();

            foreach ($installedApps->installations as $installedApp) {
                if ($installedApp->app_id == $appId) {
                    $this->zendeskAppInstance = $installedApp;
                    break;
                }
            }
        }

        return $this->zendeskAppInstance;
    }

    /**
     * Get if Zendesk App is currently installed in Zendesk support.
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function isZendeskAppInstalled(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        try {
            return $this->getZendeskAppInstance($scopeType, $scopeCode) !== null;
        } catch (\Exception $ex) {
            // Unable to query installed status. Behave as if not installed.
            return false;
        }
    }

    /**
     * Install Zendesk App
     *
     * @param string $scopeType
     * @param ?int $scopeId
     * @throws LocalizedException
     * @throws AuthException
     */
    public function installZendeskApp(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = null
    ) {
        $api = $this->apiHelper->getZendeskApiInstance($scopeType, $scopeId);

        $appId = $this->configHelper->getZendeskAppId($scopeType, $scopeId);

        $api->apps()->install([
            'app_id' => $appId,
            'settings' => $this->getZendeskAppSettings($scopeType, $scopeId)
        ]);
    }

    /**
     * Remove Zendesk App
     *
     * @param string $scopeType
     * @param int $scopeId
     * @throws AuthException
     */
    public function removeZendeskApp(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = 0
    ) {
        $appId = $this->configHelper->getZendeskAppId($scopeType, $scopeId);
        $api = $this->apiHelper->getZendeskApiInstance($scopeType, $scopeId);

        $installedApps = $api->apps()->getInstalledApps();

        $installedAppId = null;
        foreach ($installedApps->installations as $installedApp) {
            if ($installedApp->app_id == $appId) {
                $installedAppId = $installedApp->id;
                break;
            }
        }

        if ($installedAppId !== null) {
            $api->apps()->remove($installedAppId);
        }

        // else, app not installed in the first place -- nothing to do.
    }

    /**
     * Update Zendesk app settings in place.
     *
     * @param string $scopeType
     * @param int $scopeId
     * @throws LocalizedException
     * @throws IntegrationException
     * @throws NoSuchEntityException
     * @throws AuthException
     */
    public function updateZendeskAppConfiguration(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = 0
    ) {
        if (!$this->isZendeskAppInstalled($scopeType, $scopeId)) {
            return; // No app to update -- bail.
        }

        $api = $this->apiHelper->getZendeskApiInstance($scopeType, $scopeId);

        $api->apps()->updateInstallation(
            $this->getZendeskAppInstance($scopeType, $scopeId)->id,
            ['settings' => $this->getZendeskAppSettings($scopeType, $scopeId)]
        );
    }

    /**
     * Get settings for use when installing Zendesk App
     *
     * @param string $scopeType
     * @param int $scopeId
     * @return array
     * @throws LocalizedException
     * @throws IntegrationException
     * @throws NoSuchEntityException
     */
    protected function getZendeskAppSettings(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = 0
    ) {
        switch ($scopeType) {
            case \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE:
                $storeContext = $this->storeRepository->getById(
                    $this->storeGroupRepository->get(
                        $this->websiteRepository->getById($scopeId)->getDefaultGroupId()
                    )->getDefaultStoreId()
                );
                break;
            case \Magento\Store\Model\ScopeInterface::SCOPE_STORE:
                $storeContext = $this->storeRepository->getById($scopeId);
                break;
            default:
                $storeContext = $this->helper->getDefaultStore();
                break;
        }

        if (!($storeContext instanceof \Magento\Store\Model\Store)) {
            throw new LocalizedException(
                __('Store data object implementation is not compatible with Zendesk integration.')
            );
        }

        return [
            'name' => __('Magento 2 Connector'),

            // API info
            'magentoBaseUrl' => $storeContext->getBaseUrl(),
            'apiToken' => $this->integrationHelper->getAuthToken($scopeType, $scopeId),

            // App settings
            'displayName' => $this->configHelper->getZendeskAppDisplayName($scopeType, $scopeId),
            'displayOrderStatus' => $this->configHelper->getZendeskAppDisplayOrderStatus($scopeType, $scopeId),
            'displayOrderStore' => $this->configHelper->getZendeskAppDisplayOrderStore($scopeType, $scopeId),
            'displayItemQuantity' => $this->configHelper->getZendeskAppDisplayItemQuantity($scopeType, $scopeId),
            'displayItemPrice' => $this->configHelper->getZendeskAppDisplayItemPrice($scopeType, $scopeId),
            'displayTotalPrice' => $this->configHelper->getZendeskAppDisplayTotalPrice($scopeType, $scopeId),
            'displayShippingAddress' => $this->configHelper->getZendeskAppDisplayShippingAddress($scopeType, $scopeId),
            'displayShippingMethod' => $this->configHelper->getZendeskAppDisplayShippingMethod($scopeType, $scopeId),
            'displayTrackingNumber' => $this->configHelper->getZendeskAppDisplayTrackingNumber($scopeType, $scopeId),
            'displayOrderComments' => $this->configHelper->getZendeskAppDisplayOrderComments($scopeType, $scopeId)
        ];
    }
}
