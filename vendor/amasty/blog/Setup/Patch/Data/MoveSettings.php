<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Setup\Patch\Data;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\NonTransactionableInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class MoveSettings implements DataPatchInterface, NonTransactionableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var ConfigInterface
     */
    private $resourceConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $settingMap = [
        'amblog/post/display_categories' => 'amblog/category/display_categories',
        'amblog/post/categories_limit' => 'amblog/category/categories_limit'
    ];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        StoreManagerInterface $storeManager,
        ConfigInterface $resourceConfig,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->resourceConfig = $resourceConfig;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): self
    {
        foreach ($this->storeManager->getStores(true) as $store) {
            foreach ($this->settingMap as $old => $new) {
                $value = $this->scopeConfig->getValue($old, ScopeInterface::SCOPE_STORES, $store->getId());
                if ($value !== null) {
                    $this->resourceConfig->saveConfig($new, $value, ScopeInterface::SCOPE_STORES, $store->getId());
                }
            }
        }

        return $this;
    }
}
