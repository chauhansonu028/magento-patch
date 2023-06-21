<?php

namespace Zendesk\Zendesk\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider
{
    public const XML_PATH_AGENT_DOMAIN = 'zendesk/general/domain';
    public const XML_PATH_AGENT_EMAIL = 'zendesk/general/email';
    public const XML_PATH_AGENT_PASSWORD = 'zendesk/general/password';

    public const XML_PATH_EVENT_ORDER_SHIPPED = 'sunshine/events/order_shipped';
    public const XML_PATH_EVENT_CART_ADD_ITEMS = 'sunshine/events/cart_add_items';
    public const XML_PATH_EVENT_CART_REMOVE_ITEMS = 'sunshine/events/cart_remove_items';
    public const XML_PATH_EVENT_REFUND_STATUS = 'sunshine/events/refund_status';
    public const XML_PATH_EVENT_CHECKOUT_BEGIN = 'sunshine/events/checkout_begin';
    public const XML_PATH_EVENT_CUSTOMER_CREATE_UPDATE = 'sunshine/events/customer_create_update';
    public const XML_PATH_EVENT_CUSTOMER_DELETE = 'sunshine/events/customer_delete';
    public const XML_PATH_EVENT_ORDER_CREATE_UPDATE = 'sunshine/events/order_placed_updated';
    public const XML_PATH_EVENT_ORDER_CANCEL = 'sunshine/events/order_cancel';
    public const XML_PATH_EVENT_ORDER_PAID = 'sunshine/events/order_paid';

    public const XML_PATH_CORS_ORIGIN_PATTERN = 'sunshine/general/cors_origin_pattern';
    public const XML_PATH_DEBUG_ENABLED = 'sunshine/debug/enable_debug_logging';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * ConfigProvider constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get config value
     *
     * @param string $path
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return mixed
     */
    public function getValue($path, $scopeType = ScopeInterface::SCOPE_WEBSITE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue($path, $scopeType, $scopeCode);
    }

    /**
     * Is flag set
     *
     * @param string $path
     * @param string $scopeType
     * @return bool
     */
    public function isSetFlag($path, $scopeType = ScopeInterface::SCOPE_WEBSITE)
    {
        return $this->scopeConfig->isSetFlag($path, $scopeType);
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
        return (string)$this->scopeConfig->getValue(self::XML_PATH_CORS_ORIGIN_PATTERN);
    }
}
