<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Setup\Patch\Data;

use Amasty\Blog\Api\Data\AuthorInterface;
use Amasty\Blog\Api\Data\CategoryInterface;
use Amasty\Blog\Api\Data\TagInterface;
use Amasty\Blog\Model\ResourceModel\Author;
use Amasty\Blog\Model\ResourceModel\Categories;
use Amasty\Blog\Model\ResourceModel\Tag;
use Amasty\Blog\Setup\Patch\DeclarativeSchemaApplyBefore\ExtractUrlKeysFromBlogEntities;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\NonTransactionableInterface;
use Magento\Store\Model\Store;

class MoveUrlKeyToStoreTable implements DataPatchInterface, NonTransactionableInterface
{
    public const BATCH_SIZE = 500;

    public const ENTITIES_TABLES = [
        CategoryInterface::CATEGORY_ID => Categories::STORE_TABLE_NAME,
        AuthorInterface::AUTHOR_ID => Author::STORE_TABLE_NAME,
        TagInterface::TAG_ID => Tag::STORE_TABLE_NAME
    ];

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        $temporaryTableName = $this->moduleDataSetup->getTable($this->getTempTableName());
        $connection = $this->moduleDataSetup->getConnection();

        if ($connection->isTableExists($temporaryTableName)) {
            try {
                $connection->beginTransaction();

                foreach (static::ENTITIES_TABLES as $entityIdentifier => $tableName) {
                    $tableName = $this->moduleDataSetup->getTable($tableName);

                    foreach ($this->getEntitiesForUpdate($entityIdentifier) as $entityData) {
                        $urlKey = $entityData[ExtractUrlKeysFromBlogEntities::URL_KEY];
                        $connection->update(
                            $tableName,
                            [ExtractUrlKeysFromBlogEntities::URL_KEY => $urlKey],
                            sprintf(
                                '%s = %d AND %s = %d',
                                $entityIdentifier,
                                (int)$entityData['entity_id'],
                                'store_id',
                                Store::DEFAULT_STORE_ID
                            )
                        );
                    }
                }

                $connection->commit();
                $connection->dropTable($temporaryTableName);
            } catch (\Exception $e) {
                $connection->rollBack();
                throw $e;
            }
        }

        return $this;
    }

    private function getEntitiesForUpdate(string $type): iterable
    {
        $select = $this->getConnection()->select();
        $select
            ->from($this->moduleDataSetup->getTable($this->getTempTableName()))
            ->where('type = ?', $type);
        $rowsCount = $this->getRowsCount($select);
        $pageAmount = (int) ceil($rowsCount / self::BATCH_SIZE);

        for ($currentPage = 1; $currentPage <= $pageAmount; $currentPage++) {
            $select->limitPage($currentPage, self::BATCH_SIZE);

            yield from $this->getConnection()->fetchAll($select) ?: [];
        }
    }

    private function getConnection(): AdapterInterface
    {
        return $this->moduleDataSetup->getConnection();
    }

    private function getRowsCount(Select $select): int
    {
        $countSelect = clone $select;
        $countSelect->reset(Select::ORDER);
        $countSelect->reset(Select::LIMIT_COUNT);
        $countSelect->reset(Select::LIMIT_OFFSET);
        $countSelect->reset(Select::COLUMNS);
        $countSelect->columns(['count' => new \Zend_Db_Expr('COUNT(*)')]);

        return (int)$this->getConnection()->fetchOne($countSelect);
    }

    protected function getTempTableName(): string
    {
        return ExtractUrlKeysFromBlogEntities::TEMPORARY_TABLE_NAME;
    }
}
