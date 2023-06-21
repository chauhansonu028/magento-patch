<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\ResourceModel\Posts\Save;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Model\Posts;
use Amasty\Blog\Model\ResourceModel\Posts\RelatedProducts\GetPostRelatedProducts;
use Magento\Framework\App\ResourceConnection;

class SavePostProductRelations implements SavePartInterface
{
    const DATA_SECTION = 'related_products_container';
    const ENTITY_ID = 'entity_id';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(Posts $model): void
    {
        if ($model->hasData(self::DATA_SECTION)) {
            $connection = $this->resourceConnection->getConnection();
            $table = $this->resourceConnection->getTableName(
                GetPostRelatedProducts::POST_PRODUCT_RELATION_TABLE
            );
            $postId = (int)$model->getPostId();
            $connection->delete($table, sprintf('%s = %d', PostInterface::POST_ID, $postId));
            $relatedProductsData = $model->getData(self::DATA_SECTION);

            if (!empty($relatedProductsData)) {
                $dataForSave = $this->prepareDataForSave($postId, $relatedProductsData);
                $connection->insertMultiple($table, $dataForSave);
            }
        }
    }

    /**
     * @param int $postId
     * @param array[] $relatedProductsData
     *
     * @return array[]
     */
    private function prepareDataForSave(int $postId, array $relatedProductsData): array
    {
        return array_map(function (array $relateProductItem) use ($postId): array {
            return [
                GetPostRelatedProducts::POST_ID => $postId,
                GetPostRelatedProducts::POSITION => (int) $relateProductItem[GetPostRelatedProducts::POSITION_ALIAS],
                GetPostRelatedProducts::PRODUCT_ID => (int) $relateProductItem[self::ENTITY_ID]
            ];
        }, $relatedProductsData);
    }
}
