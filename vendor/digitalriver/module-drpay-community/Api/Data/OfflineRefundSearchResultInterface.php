<?php

declare(strict_types=1);

namespace Digitalriver\DrPay\Api\Data;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

interface OfflineRefundSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get result items
     * @return OfflineRefundInterface[]
     */
    public function getItems(): array;

    /**
     * Set result items
     * @param OfflineRefundInterface[] $items
     * @return $this
     */
    public function setItems(array $items): self;
}
