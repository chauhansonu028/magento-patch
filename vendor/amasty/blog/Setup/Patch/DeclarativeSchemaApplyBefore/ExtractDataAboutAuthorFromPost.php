<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Setup\Patch\DeclarativeSchemaApplyBefore;

use Amasty\Blog\Api\Data\AuthorInterface;
use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Model\ResourceModel\Author;
use Amasty\Blog\Model\ResourceModel\Posts as PostsResource;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class ExtractDataAboutAuthorFromPost implements PatchInterface
{
    const TEMPORARY_TABLE_NAME = 'amasty_blog_author_tmp';

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
        if ($this->isCanApply()) {
            $connection = $this->schemaSetup->getConnection();
            $postTableName = $this->schemaSetup->getTable(PostsResource::TABLE_NAME);
            $affectedColumns = [
                PostInterface::POST_ID,
                PostInterface::POSTED_BY,
                AuthorInterface::FACEBOOK_PROFILE,
                AuthorInterface::TWITTER_PROFILE
            ];
            $select = $connection->select()->from(
                $postTableName,
                $affectedColumns
            );
            $this->createTemporaryTable();
            $connection->query(
                $connection->insertFromSelect(
                    $select,
                    $this->schemaSetup->getTable(self::TEMPORARY_TABLE_NAME),
                    $affectedColumns
                )
            );
        }

        return $this;
    }

    private function isCanApply(): bool
    {
        $connection = $this->schemaSetup->getConnection();
        $postTableName = $this->schemaSetup->getTable(PostsResource::TABLE_NAME);
        $authorTableName = $this->schemaSetup->getTable(Author::TABLE_NAME);

        return $connection->isTableExists($postTableName)
            && !$connection->isTableExists($authorTableName)
            && $connection->tableColumnExists($postTableName, PostInterface::POSTED_BY);
    }

    private function createTemporaryTable()
    {
        $this->schemaSetup->startSetup();
        $connection = $this->schemaSetup->getConnection();
        $table = $connection->newTable($this->schemaSetup->getTable(self::TEMPORARY_TABLE_NAME));
        $table->addColumn(
            PostInterface::POST_ID,
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_UNSIGNED => true,
            ]
        );
        $table->addColumn(
            PostInterface::POSTED_BY,
            Table::TYPE_TEXT,
            null,
            [
                Table::OPTION_NULLABLE => true
            ]
        );
        $table->addColumn(
            AuthorInterface::FACEBOOK_PROFILE,
            Table::TYPE_TEXT,
            null,
            [
                Table::OPTION_NULLABLE => true
            ]
        );
        $table->addColumn(
            AuthorInterface::TWITTER_PROFILE,
            Table::TYPE_TEXT,
            null,
            [
                Table::OPTION_NULLABLE => true
            ]
        );
        $connection->createTable($table);
        $this->schemaSetup->endSetup();
    }
}
