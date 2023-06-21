<?php

namespace Digitalriver\DrPay\Model\OfflineRefund;

use Digitalriver\DrPay\Api\Data\OfflineRefundInterface;
use Digitalriver\DrPay\Api\Data\OfflineRefundSearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Search result class for OfflineRefund
 */
class SearchResult implements OfflineRefundSearchResultInterface
{
    /**
     * @var OfflineRefundInterface[]
     */
    private $items;

    /**
     * @var SearchCriteriaInterface
     */
    private $searchCriteria;

    /**
     * @var int
     */
    private $totalCount = 0;

    /**
     * Get result items
     * @return OfflineRefundInterface[]
     */
    public function getItems(): array
    {
        return $this->items ?? [];
    }

    /**
     * Set result items
     * @param OfflineRefundInterface[] $items
     * @return void
     */
    public function setItems(array $items): OfflineRefundSearchResultInterface
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Get search criteria
     *
     * @return SearchCriteriaInterface
     */
    public function getSearchCriteria(): SearchCriteriaInterface
    {
        return $this->searchCriteria;
    }

    /**
     * Set search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return OfflineRefundSearchResultInterface
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria): OfflineRefundSearchResultInterface
    {
        $this->searchCriteria = $searchCriteria;

        return $this;
    }

    /**
     * Get total count
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * Set total count
     * @param int $totalCount
     * @return OfflineRefundSearchResultInterface
     */
    public function setTotalCount($totalCount): OfflineRefundSearchResultInterface
    {
        $this->totalCount = $totalCount;

        return $this;
    }
}
