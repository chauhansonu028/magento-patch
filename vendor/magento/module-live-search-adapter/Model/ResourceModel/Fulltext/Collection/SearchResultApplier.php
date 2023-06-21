<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model\ResourceModel\Fulltext\Collection;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\DB\Select;

/**
 * Apply search results
 */
class SearchResultApplier implements SearchResultApplierInterface
{
    /**
     * @var Collection
     */
    private Collection $collection;

    /**
     * @var SearchResultInterface
     */
    private SearchResultInterface $searchResult;

    /**
     * @param Collection $collection
     * @param SearchResultInterface $searchResult
     */
    public function __construct(
        Collection $collection,
        SearchResultInterface $searchResult
    ) {
        $this->collection = $collection;
        $this->searchResult = $searchResult;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        if (empty($this->searchResult->getItems())) {
            $this->collection->getSelect()->where('NULL');
            return;
        }
        $ids = [];
        foreach ($this->searchResult->getItems() as $item) {
            $ids[] = $item->getId();
        }

        $orderList = implode(',', $ids);
        $this->collection->getSelect()->where('e.entity_id IN (?)', $ids);
        $this->collection->getSelect()
            ->where('e.entity_id IN (?)', $ids)
            ->reset(Select::ORDER)
            ->order(new \Zend_Db_Expr("FIELD(e.entity_id, $orderList)"));
    }
}
