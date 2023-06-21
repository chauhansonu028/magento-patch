<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Setup;

use Amasty\Blog\Api\Data\VoteInterface;
use Amasty\Blog\Model\ResourceModel\Author;
use Amasty\Blog\Model\ResourceModel\Categories;
use Amasty\Blog\Model\ResourceModel\Categories\Collection;
use Amasty\Blog\Model\ResourceModel\Comments;
use Amasty\Blog\Model\ResourceModel\Posts;
use Amasty\Blog\Model\ResourceModel\Posts\RelatedProducts\GetPostRelatedProducts;
use Amasty\Blog\Model\ResourceModel\Tag;
use Amasty\Blog\Model\ResourceModel\View;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    const MODULE_TABLES = [
        Author::STORE_TABLE_NAME,
        Author::TABLE_NAME,
        Collection::CATEGORY_POST_RELATION_TABLE,
        Categories::STORE_TABLE_FIELDS,
        Categories::TABLE_NAME,
        Comments::TABLE_NAME,
        Posts::POSTS_STORE_TABLE,
        Posts::POSTS_TAGS_RELATION_TABLE,
        Posts::TABLE_NAME,
        Tag::TABLE_NAME,
        Tag::STORE_TABLE_NAME,
        View::TABLE_NAME,
        VoteInterface::MAIN_TABLE,
        GetPostRelatedProducts::POST_PRODUCT_RELATION_TABLE
    ];

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $connection = $setup->getConnection();

        foreach (self::MODULE_TABLES as $table) {
            $connection->dropTable($setup->getTable($table));
        }

        $setup->endSetup();
    }
}
