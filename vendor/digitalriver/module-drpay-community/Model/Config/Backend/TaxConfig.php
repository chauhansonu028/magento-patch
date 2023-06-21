<?php

namespace Digitalriver\DrPay\Model\Config\Backend;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class TaxConfig implements ConfigProviderInterface
{
    public const TAX_CONFIG_ENABLED = "dr_settings/tax_conf/active";

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->scopeConfig->getValue(
            self::TAX_CONFIG_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
