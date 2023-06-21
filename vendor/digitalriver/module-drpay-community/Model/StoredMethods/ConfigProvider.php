<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\StoredMethods;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider
{
    private const STORED_METHODS_ENABLED = "dr_settings/stored_methods/enable";

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::STORED_METHODS_ENABLED, ScopeInterface::SCOPE_WEBSITE);
    }
}
