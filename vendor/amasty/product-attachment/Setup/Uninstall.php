<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Setup;

use Amasty\ProductAttachment\Model\File\ResourceModel\File;
use Amasty\ProductAttachment\Model\Icon\ResourceModel\Icon;
use Amasty\ProductAttachment\Model\Import\ResourceModel\Import;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    public const TABLE_NAMES = [
        File::TABLE_NAME,
        Icon::TABLE_NAME,
        Icon::ICON_EXTENSION_TABLE_NAME,
        File::FILE_STORE_TABLE_NAME,
        File::FILE_STORE_CATEGORY_TABLE_NAME,
        File::FILE_STORE_PRODUCT_TABLE_NAME,
        File::FILE_STORE_CATEGORY_PRODUCT_TABLE_NAME,
        File::REPORT_TABLE_NAME,
        Import::TABLE_NAME
    ];

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this
            ->uninstallTables($setup)
            ->uninstallConfigData($setup);
    }

    private function uninstallTables(SchemaSetupInterface $setup): self
    {
        $setup->startSetup();
        foreach (self::TABLE_NAMES as $tableName) {
            $setup->getConnection()->dropTable($setup->getTable($tableName));
        }
        $setup->endSetup();

        return $this;
    }

    private function uninstallConfigData(SchemaSetupInterface $setup): self
    {
        $configTable = $setup->getTable('core_config_data');
        $setup->getConnection()->delete($configTable, "`path` LIKE 'amfile/%'");

        return $this;
    }
}
