<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Setup\Patch\DeclarativeSchemaApplyBefore;

use Amasty\Blog\Api\Data\AuthorInterface;
use Amasty\Blog\Api\Data\CategoryInterface;
use Amasty\Blog\Api\Data\TagInterface;
use Amasty\Blog\Model\ResourceModel\Author;
use Amasty\Blog\Model\ResourceModel\Categories;
use Amasty\Blog\Model\ResourceModel\Tag;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class ExtractUrlKeysFromBlogEntities implements PatchInterface
{
    public const ENTITIES_TABLES = [
        CategoryInterface::CATEGORY_ID => Categories::TABLE_NAME,
        AuthorInterface::AUTHOR_ID => Author::TABLE_NAME,
        TagInterface::TAG_ID => Tag::TABLE_NAME
    ];

    public const TEMPORARY_TABLE_NAME = 'amasty_blog_entities_url_keys';
    public const URL_KEY = 'url_key';

    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    public function __construct(
        SchemaSetupInterface $schemaSetup
    ) {
        $this->schemaSetup = $schemaSetup;
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
        $connection = $this->getConnection();

        if ($this->isCanApply()) {
            $tmpTableName = $this->createTemporaryTable();
            $connection->beginTransaction();

            try {
                foreach ($this->prepareSelects() as $select) {
                    $connection->query($connection->insertFromSelect($select, $tmpTableName));
                }

                $connection->commit();
            } catch (\Exception $e) {
                $connection->rollBack();
                throw $e;
            }
        }

        return $this;
    }

    private function isCanApply(): bool
    {
        return array_reduce(static::ENTITIES_TABLES, function (bool $carry, string $tableName): bool {
            $tableName = $this->schemaSetup->getTable($tableName);
            $connection = $this->getConnection();

            return $carry && $connection->isTableExists($tableName) && $connection->tableColumnExists(
                $tableName,
                static::URL_KEY
            );
        }, true);
    }

    private function getConnection(): AdapterInterface
    {
        return $this->schemaSetup->getConnection();
    }

    /**
     * @return Select[]
     */
    private function prepareSelects(): array
    {
        $selects = [];

        foreach (static::ENTITIES_TABLES as $tableIdentifier => $tableName) {
            $select = $this->getConnection()->select();
            $selects[] = $select->from(
                $this->schemaSetup->getTable($tableName),
                [
                    static::URL_KEY => static::URL_KEY,
                    'entity_id' => $tableIdentifier,
                    'type' => new \Zend_Db_Expr("'{$tableIdentifier}'")
                ]
            );
        }

        return $selects;
    }

    private function createTemporaryTable(): string
    {
        $this->schemaSetup->startSetup();
        $connection = $this->getConnection();
        $tableName = $this->schemaSetup->getTable(static::TEMPORARY_TABLE_NAME);
        $table = $connection->newTable($tableName);
        $table->addColumn(
            static::URL_KEY,
            Table::TYPE_TEXT,
            null,
            [
                Table::OPTION_NULLABLE => true,
                Table::OPTION_LENGTH => 255
            ]
        );
        $table->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_NULLABLE => false
            ]
        );
        $table->addColumn(
            'type',
            Table::TYPE_TEXT,
            null,
            [
                Table::OPTION_NULLABLE => true,
                Table::OPTION_LENGTH => 255
            ]
        );
        $connection->createTable($table);
        $this->schemaSetup->endSetup();

        return $tableName;
    }
}
