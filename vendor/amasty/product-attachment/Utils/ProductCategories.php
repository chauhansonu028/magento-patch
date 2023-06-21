<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Utils;

use Magento\Catalog\Model\Indexer\Category\Product\AbstractAction;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;

class ProductCategories
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @var IndexScopeResolver
     */
    private $tableResolver;

    public function __construct(
        ResourceConnection $resourceConnection,
        DimensionFactory $dimensionFactory,
        IndexScopeResolver $tableResolver
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->dimensionFactory = $dimensionFactory;
        $this->tableResolver = $tableResolver;
    }

    public function getCategoryIdsByProduct(int $productId, int $storeId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $categoryProductTable = $this->getCatalogCategoryProductTableName($storeId);
        $storeTable = $this->resourceConnection->getTableName(Store::ENTITY);
        $storeGroupTable = $this->resourceConnection->getTableName(Group::ENTITY);

        $select = $connection->select()
            ->from(['cat_index' => $categoryProductTable], ['category_id'])
            ->joinInner(['store' => $storeTable], $connection->quoteInto('store.store_id = ?', $storeId), [])
            ->joinInner(
                ['store_group' => $storeGroupTable],
                'store.group_id = store_group.group_id AND cat_index.category_id != store_group.root_category_id',
                []
            )
            ->where('product_id = ?', $productId);

        return $connection->fetchCol($select);
    }

    private function getCatalogCategoryProductTableName(int $storeId): string
    {
        $dimension = $this->dimensionFactory->create(Store::ENTITY, (string)$storeId);

        return $this->tableResolver->resolve(
            AbstractAction::MAIN_INDEX_TABLE,
            [$dimension]
        );
    }
}
