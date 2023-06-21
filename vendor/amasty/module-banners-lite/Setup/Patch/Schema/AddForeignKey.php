<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Setup\Patch\Schema;

use Amasty\BannersLite\Api\Data\BannerInterface;
use Amasty\BannersLite\Api\Data\BannerRuleInterface;
use Amasty\BannersLite\Model\ResourceModel\Banner;
use Amasty\BannersLite\Model\ResourceModel\BannerRule;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\SalesRule\Api\Data\RuleInterface as SalesRuleInterface;

/**
 * Add a foreign key for salesrule table according to primary key
 */
class AddForeignKey implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var MetadataPool
     */
    private $metadata;

    public function __construct(
        MetadataPool $metadata,
        ModuleDataSetupInterface $setup
    ) {
        $this->setup = $setup;
        $this->metadata = $metadata;
    }

    /**
     * @return void
     */
    public function apply(): void
    {
        $this->addFkToTable(Banner::TABLE_NAME, BannerInterface::SALESRULE_ID);
        $this->addFkToTable(BannerRule::TABLE_NAME, BannerRuleInterface::SALESRULE_ID);
    }

    /**
     * @param string $tableName
     * @param string $fkColumnIndex
     * @throws \Exception
     */
    public function addFkToTable(string $tableName, string $fkColumnIndex): void
    {
        /** @var AdapterInterface $adapter */
        $adapter = $this->setup->getConnection();
        $salesruleTableName = $this->setup->getTable('salesrule');
        $tableName = $this->setup->getTable($tableName);
        $foreignKeys = $adapter->getForeignKeys($tableName);
        $linkField = $this->metadata->getMetadata(SalesRuleInterface::class)->getLinkField();
        $fkName = $adapter->getForeignKeyName(
            $tableName,
            $fkColumnIndex,
            $salesruleTableName,
            $linkField
        );
        
        if (!empty($foreignKeys)) {
            return;
        }

        $adapter->addForeignKey(
            $fkName,
            $tableName,
            $fkColumnIndex,
            $salesruleTableName,
            $linkField
        );
    }

    /**
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
