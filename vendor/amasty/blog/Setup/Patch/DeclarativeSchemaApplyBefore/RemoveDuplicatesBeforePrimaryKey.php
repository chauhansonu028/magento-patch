<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Setup\Patch\DeclarativeSchemaApplyBefore;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Clear duplicates by columns
 *
 * Tables:
 * - amasty_blog_categories_store
 * - amasty_blog_posts_store
 * - amasty_blog_posts_category
 * _ amasty_blog_posts_tag
 * _ amasty_blog_tags_store
 * _ amasty_blog_author_store
 */
class RemoveDuplicatesBeforePrimaryKey implements PatchInterface
{
    /**
     * @var array
     */
    private $columnsForPrimaryKey = [
        'amasty_blog_categories_store' => [
            'category_id',
            'store_id'
        ],
        'amasty_blog_posts_store' => [
            'post_id',
            'store_id'
        ],
        'amasty_blog_posts_category' => [
            'post_id',
            'category_id'
        ],
        'amasty_blog_posts_tag' => [
            'post_id',
            'tag_id'
        ],
        'amasty_blog_tags_store' => [
            'tag_id',
            'store_id'
        ],
        'amasty_blog_author_store' => [
            'author_id',
            'store_id'
        ]
    ];

    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    public function __construct(SchemaSetupInterface $schemaSetup)
    {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [
            MakePrimaryFieldsNonNullable::class
        ];
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return $this
     */
    public function apply()
    {
        $this->schemaSetup->startSetup();
        $connection = $this->schemaSetup->getConnection();
        foreach ($this->columnsForPrimaryKey as $tableName => $columns) {
            $tableNameWithPrefix = $this->schemaSetup->getTable($tableName);
            if (!$connection->isTableExists($tableNameWithPrefix)
                || !$this->isTableHasDuplicates($tableNameWithPrefix, $columns)
            ) {
                continue;
            }

            $connection->createTable(
                $connection->createTableByDdl($tableNameWithPrefix, $this->getTempTableName($tableNameWithPrefix))
            );

            $this->moveData($tableNameWithPrefix, $this->getTempTableName($tableNameWithPrefix), $columns);
            $connection->delete($tableNameWithPrefix);
            $this->moveData($this->getTempTableName($tableNameWithPrefix), $tableNameWithPrefix, $columns);
            $connection->dropTable($this->getTempTableName($tableNameWithPrefix));
        }
        $this->schemaSetup->endSetup();

        return $this;
    }

    private function getTempTableName(string $tableName): string
    {
        return $tableName . '_temp';
    }

    private function isTableHasDuplicates(string $tableName, array $columns): bool
    {
        $connection = $this->schemaSetup->getConnection();
        $select = $connection->select()
            ->from($tableName)
            ->group($columns)
            ->having('COUNT(*) > 1')
            ->limit(1);
        return (bool) $connection->fetchOne($select);
    }

    private function moveData(string $fromTable, string $toTable, array $columns): void
    {
        $connection = $this->schemaSetup->getConnection();

        $selectToMove = $connection->select()->from([
            'from' => $fromTable
        ])->group(
            $columns
        )->order(
            $columns
        );

        $pageLimit = 100000;
        $page = 1;
        while (true) {
            $selectToMove->limitPage($page, $pageLimit);
            $result = $connection->query($connection->insertFromSelect($selectToMove, $toTable));
            if (!$result->rowCount() || $result->rowCount() < 0.9 * $pageLimit) {
                break;
            }
            $page++;
        }
    }
}
