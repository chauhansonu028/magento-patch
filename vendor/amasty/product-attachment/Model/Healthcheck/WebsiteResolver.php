<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Healthcheck;

use Amasty\ProductAttachment\Model\File\ResourceModel\File;
use Magento\Framework\App\ResourceConnection;

class WebsiteResolver
{
    public const PRODUCT_WEBSITE_RELATION_TABLE = 'catalog_product_website';
    public const CATEGORY_TABLE = 'catalog_category_entity';
    public const STORE_GROUP_TABLE = 'store_group';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    public function getAssociatedWebsites(int $attachmentId): array
    {
        return array_unique(array_merge(
            $this->getAssignedProductsWebsites($attachmentId),
            $this->getAssignedCategoriesWebsites($attachmentId)
        ));
    }

    private function getAssignedProductsWebsites(int $attachmentId): array
    {
        $select = $this->resourceConnection->getConnection()->select()->distinct()
            ->from(
                ['file_product' => $this->resourceConnection->getTableName(
                    File::FILE_STORE_PRODUCT_TABLE_NAME
                )]
            )->joinInner(
                ['product_website' => $this->resourceConnection->getTableName(
                    self::PRODUCT_WEBSITE_RELATION_TABLE
                )],
                'file_product.product_id = product_website.product_id'
            )->where('file_product.file_id = ?', $attachmentId)
            ->reset('columns')
            ->columns('product_website.website_id');

        return $this->resourceConnection->getConnection()->fetchCol($select);
    }

    private function getAssignedCategoriesWebsites(int $attachmentId): array
    {
        $select = $this->resourceConnection->getConnection()->select()->distinct()
            ->from(
                ['file_category' => $this->resourceConnection->getTableName(
                    File::FILE_STORE_CATEGORY_TABLE_NAME
                )],
                'st_gr.website_id'
            )->joinInner(
                ['category' => $this->resourceConnection->getTableName(self::CATEGORY_TABLE)],
                'category.entity_id = file_category.category_id',
                []
            )->joinInner(
                ['root_category' => $this->resourceConnection->getTableName(self::CATEGORY_TABLE)],
                //phpcs root category path
                //phpcs:ignore Magento2.Legacy.TableName.FoundLegacyTableName
                'root_category.path = SUBSTRING_INDEX(category.path, "/", 2)',
                []
            )->joinInner(
                ['st_gr' => $this->resourceConnection->getTableName(self::STORE_GROUP_TABLE)],
                'st_gr.root_category_id = root_category.entity_id',
                []
            )->where('file_category.file_id = ?', $attachmentId);

        return $this->resourceConnection->getConnection()->fetchCol($select);
    }
}
