<?php

namespace Zendesk\Zendesk\Helper;

use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Api\StoreRepositoryInterface;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const DOMAIN_CONFIG_PATH = 'zendesk/general/domain';
    public const AGENT_EMAIL_CONFIG_PATH = 'zendesk/general/email';
    public const API_TOKEN_CONFIG_PATH = 'zendesk/general/password';
    public const ZENDESK_APP_CONFIGURED_PATH = 'zendesk/general/is_configured';
    public const WEB_WIDGET_ENABLED_CONFIG_PATH = 'zendesk/frontend_features/web_widget_code_active';
    public const ENABLE_DEBUG_LOGGING_CONFIG_PATH = 'zendesk/debug/enable_debug_logging';
    public const WEB_WIDGET_DYNAMIC_SNIPPET_URL_PATTERN_CONFIG_PATH = 'zendesk/web_widget/dynamic_snippet_url_pattern';
    public const WEB_WIDGET_CUSTOMIZE_URL_PATTERN_CONFIG_PATH = 'zendesk/web_widget/web_widget_customize_url';
    public const ZENDESK_APP_ID_CONFIG_PATH = 'zendesk/zendesk_app/app_id';
    public const ZENDESK_APP_CORS_ORIGIN_PATTERN_CONFIG_PATH = 'zendesk/zendesk_app/cors_origin_pattern';

    public const ZENDESK_APP_DISPLAY_NAME_CONFIG_PATH = 'zendesk/zendesk_app/display_name';
    public const ZENDESK_APP_DISPLAY_ORDER_STATUS_CONFIG_PATH = 'zendesk/zendesk_app/display_order_status';
    public const ZENDESK_APP_DISPLAY_ORDER_STORE_CONFIG_PATH = 'zendesk/zendesk_app/display_order_store';
    public const ZENDESK_APP_DISPLAY_ITEM_QUANTITY_CONFIG_PATH = 'zendesk/zendesk_app/display_item_quantity';
    public const ZENDESK_APP_DISPLAY_ITEM_PRICE_CONFIG_PATH = 'zendesk/zendesk_app/display_item_price';
    public const ZENDESK_APP_DISPLAY_TOTAL_PRICE_CONFIG_PATH = 'zendesk/zendesk_app/display_total_price';
    public const ZENDESK_APP_DISPLAY_SHIPPING_ADDRESS_CONFIG_PATH = 'zendesk/zendesk_app/display_shipping_address';
    public const ZENDESK_APP_DISPLAY_SHIPPING_METHOD_CONFIG_PATH = 'zendesk/zendesk_app/display_shipping_method';
    public const ZENDESK_APP_DISPLAY_TRACKING_NUMBER_CONFIG_PATH = 'zendesk/zendesk_app/display_tracking_number';
    public const ZENDESK_APP_DISPLAY_ORDER_COMMENTS_CONFIG_PATH = 'zendesk/zendesk_app/display_order_comments';

    public const API_CREDENTIAL_PATHS = [
        \Zendesk\Zendesk\Helper\Config::AGENT_EMAIL_CONFIG_PATH,
        \Zendesk\Zendesk\Helper\Config::DOMAIN_CONFIG_PATH,
        \Zendesk\Zendesk\Helper\Config::API_TOKEN_CONFIG_PATH
    ];

    public const DOMAIN_PLACEHOLDER = '{domain}';
    public const ZENDESK_DOMAIN = '.zendesk.com';

    public const BRAND_FIELD_CONFIG_PATH_PREFIX = 'brand-mapping-';
    public const BRAND_FIELD_GROUP_PREFIX = 'zendesk/brand_mapping';

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var Manager
     */
    protected $cacheManager;

    /**
     * @var StoreRepositoryInterface
     */
    protected $repository;

    /**
     * @var int
     */
    protected $zendeskAppId;

    /**
     * Config constructor.
     * @param Context $context
     * @param WriterInterface $configWriter
     * @param Manager $cacheManager
     * @param StoreRepositoryInterface $repository
     */
    public function __construct(
        Context $context,
        // End parent parameters
        WriterInterface $configWriter,
        Manager $cacheManager,
        StoreRepositoryInterface $repository
    ) {
        parent::__construct($context);
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
        $this->repository = $repository;
    }

    /**
     * Get configured zendesk domain
     *
     * @param string $scopeType
     * @param ?string $scopeCode
     * @return string
     */
    public function getDomain($scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return (string)$this->scopeConfig->getValue(self::DOMAIN_CONFIG_PATH, $scopeType, $scopeCode);
    }

    /**
     * Get configured zendesk subdomain
     *
     * @param string $scopeType
     * @param ?string $scopeCode
     * @return string
     */
    public function getSubDomain(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        $domain = $this->getDomain($scopeType, $scopeCode);

        return str_replace(self::ZENDESK_DOMAIN, '', $domain);
    }

    /**
     * Get configured zendesk agent email
     *
     * @param string $scopeType
     * @param ?string $scopeCode
     * @return string
     */
    public function getAgentEmail(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (string)$this->scopeConfig->getValue(self::AGENT_EMAIL_CONFIG_PATH, $scopeType, $scopeCode);
    }

    /**
     * Get configured zendesk API token
     *
     * @param string $scopeType
     * @param ?string $scopeCode
     * @return string
     */
    public function getApiToken(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (string)$this->scopeConfig->getValue(self::API_TOKEN_CONFIG_PATH, $scopeType, $scopeCode);
    }

    /**
     * Get if web widget enabled by store
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function getWebWidgetEnabled(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(self::WEB_WIDGET_ENABLED_CONFIG_PATH, $scopeType, $scopeCode);
    }

    /**
     * Get if API call debug info should be logged
     *
     * @return bool
     */
    public function getDebugLoggingEnabled()
    {
        return (bool)$this->scopeConfig->getValue(self::ENABLE_DEBUG_LOGGING_CONFIG_PATH);
    }

    /**
     * Get web widget dynamic snippet URL pattern.
     *
     * Currently fixed value in config.xml, but could conceivably be updated in the future.
     * Replace {domain} with full zendesk domain.
     *
     * @return string
     */
    public function getWebWidgetDynamicSnippetUrlPattern()
    {
        return (string)$this->scopeConfig->getValue(self::WEB_WIDGET_DYNAMIC_SNIPPET_URL_PATTERN_CONFIG_PATH);
    }

    /**
     * Get web widget customization URL pattern.
     *
     * Currently fixed value in config.xml, but could conceivably be updated in the future.
     *
     * @return string
     */
    public function getWebWidgetCustomizeUrlPattern()
    {
        return (string)$this->scopeConfig->getValue(self::WEB_WIDGET_CUSTOMIZE_URL_PATTERN_CONFIG_PATH);
    }

    /**
     * Get corresponding Zendesk APP ID.
     *
     * Currently fixed value in config.xml, but could conceivably be updated in the future.
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return int
     */
    public function getZendeskAppId(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        if (!$this->zendeskAppId) {
            $this->zendeskAppId = (int)$this->scopeConfig->getValue(
                self::ZENDESK_APP_ID_CONFIG_PATH,
                $scopeType,
                $scopeCode
            );
        }

        return $this->zendeskAppId;
    }

    /**
     * Generate Zendesk App Id
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return int
     */
    public function generateZendeskAppId(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
        $scopeCode = null
    ) {
        $this->zendeskAppId = (int)date("ymdHi");
        $this->saveZendeskAppId($this->zendeskAppId, $scopeType, $scopeCode);
        return $this->zendeskAppId;
    }

    /**
     * Save Zendesk App ID
     *
     * @param string $appId
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return void
     */
    public function saveZendeskAppId(
        $appId,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
        $scopeCode = null
    ) {
        $this->configWriter->save(
            self::ZENDESK_APP_ID_CONFIG_PATH,
            $appId,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get store ID(s) mapped to given brand ID
     *
     * @param int $brandId
     * @return int[]
     */
    public function getBrandStores($brandId)
    {
        $stores = $this->scopeConfig->getValue(
            self::BRAND_FIELD_GROUP_PREFIX . '/' . self::BRAND_FIELD_CONFIG_PATH_PREFIX . $brandId
        );

        return !empty($stores) ? explode(',', $stores) : null;
    }

    /**
     * Set stores associated with a given brand
     *
     * @param int $brandId
     * @param array $storeIds
     */
    public function setBrandStores($brandId, array $storeIds)
    {
        $this->configWriter->save(
            self::BRAND_FIELD_GROUP_PREFIX . '/' . self::BRAND_FIELD_CONFIG_PATH_PREFIX . $brandId,
            implode(',', $storeIds)
        );
    }

    /**
     * Row in DB for a given brand.
     *
     * @param int $brandId
     */
    public function deleteBrandStores($brandId)
    {
        $this->configWriter->delete(
            self::BRAND_FIELD_GROUP_PREFIX . '/' . self::BRAND_FIELD_CONFIG_PATH_PREFIX . $brandId
        );
    }

    /**
     * Delete widget snippets
     *
     * @return void
     */
    public function deleteWidgetSnippets()
    {
        $stores = $this->repository->getList();
        foreach ($stores as $store) {
            $id = (int)$store->getId();
            // delete current widget snippet
            $this->configWriter->delete(
                \Zendesk\Zendesk\Helper\WebWidget::WEB_WIDGET_SNIPPET_CACHE_CONFIG_PATH,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
                $id
            );
        }
    }

    /**
     * Get regex pattern for valid CORS origins for Zendesk app.
     *
     * Currently fixed value in config.xml, but could conceivably be updated in the future.
     *
     * @return string
     */
    public function getZendeskAppCorsOrigin()
    {
        return (string)$this->scopeConfig->getValue(self::ZENDESK_APP_CORS_ORIGIN_PATTERN_CONFIG_PATH);
    }

    /**
     * Get if Zendesk app should display customer name
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function getZendeskAppDisplayName(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_APP_DISPLAY_NAME_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get if Zendesk app should display order status
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function getZendeskAppDisplayOrderStatus(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_APP_DISPLAY_ORDER_STATUS_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get if Zendesk app should display order store
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function getZendeskAppDisplayOrderStore(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_APP_DISPLAY_ORDER_STORE_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get if Zendesk app should display item quantity
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function getZendeskAppDisplayItemQuantity(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_APP_DISPLAY_ITEM_QUANTITY_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get if Zendesk app should display item price
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function getZendeskAppDisplayItemPrice(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_APP_DISPLAY_ITEM_PRICE_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get if Zendesk app should display total price
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function getZendeskAppDisplayTotalPrice(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_APP_DISPLAY_TOTAL_PRICE_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get if Zendesk app should display shipping address
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function getZendeskAppDisplayShippingAddress(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_APP_DISPLAY_SHIPPING_ADDRESS_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get if Zendesk app should display shipping method
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function getZendeskAppDisplayShippingMethod(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_APP_DISPLAY_SHIPPING_METHOD_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get if Zendesk app should display tracking number(s)
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function getZendeskAppDisplayTrackingNumber(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_APP_DISPLAY_TRACKING_NUMBER_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get if Zendesk app should display order comments
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function getZendeskAppDisplayOrderComments(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_APP_DISPLAY_ORDER_COMMENTS_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Check is Zendesk is configured
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function isZendeskAppConfigured(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        $path = self::ZENDESK_APP_CONFIGURED_PATH . '_' . $scopeType . ($scopeCode ? '_' . $scopeCode : '');
        return (bool)$this->scopeConfig->getValue(
            $path,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Set Zendesk configured flag
     *
     * @param mixed $isConfigured
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return void
     */
    public function setZendeskAppConfigured(
        $isConfigured,
        $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        $path = self::ZENDESK_APP_CONFIGURED_PATH . '_' . $scopeType . ($scopeCode ? '_' . $scopeCode : '');
        $this->configWriter->save(
            $path,
            (bool)$isConfigured,
            $scopeType,
            (int)$scopeCode
        );
    }
}
