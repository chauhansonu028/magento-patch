<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchProductListing\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Configuration for Live Search features.
 */
class Config
{
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var bool[]
     */
    private array $configCache = [];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Returns if specific configuration is active
     *
     * @param string $configPath
     * @return bool
     */
    public function isActive(
        string $configPath
    ): bool {
        if (isset($this->configCache[$configPath])) {
            return $this->configCache[$configPath];
        }

        $active = false;
        $scopeCode = null;

        try {
            $scopeCode = $this->storeManager->getStore()->getId();
            $scopeType = ScopeInterface::SCOPE_STORE;
        } catch (NoSuchEntityException $exception) {
            // unable to find current store view id
            $scopeType = \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT;
        }

        if (!empty($scopeCode)) {
            $active = $this->scopeConfig->isSetFlag($configPath, $scopeType, $scopeCode);
        }

        $this->configCache[$configPath] = $active;

        return $this->configCache[$configPath];
    }
}
