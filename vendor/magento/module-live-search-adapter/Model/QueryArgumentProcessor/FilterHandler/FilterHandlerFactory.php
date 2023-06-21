<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model\QueryArgumentProcessor\FilterHandler;

use Magento\Framework\Api\SearchCriteriaInterface;

class FilterHandlerFactory
{
    /**
     * @var RangeFilterHandlerFactory
     */
    private RangeFilterHandlerFactory $rangeFilterHandlerFactory;

    /**
     * @var CategoryFilterHandlerFactory
     */
    private CategoryFilterHandlerFactory $categoryFilterHandlerFactory;

    /**
     * @var VisibilityFilterHandlerFactory
     */
    private VisibilityFilterHandlerFactory $visibilityFilterHandlerFactory;

    /**
     * @var AttributeFilterHandlerFactory
     */
    private AttributeFilterHandlerFactory $attributeFilterHandlerFactory;

    /**
     * @var DefaultAttributeFilterHandlerFactory
     */
    private DefaultAttributeFilterHandlerFactory $defaultAttributeFilterHandlerFactory;

    /**
     * @param RangeFilterHandlerFactory $rangeFilterHandlerFactory
     * @param CategoryFilterHandlerFactory $categoryFilterHandlerFactory
     * @param VisibilityFilterHandlerFactory $visibilityFilterHandlerFactory
     * @param AttributeFilterHandlerFactory $attributeFilterHandlerFactory
     * @param DefaultAttributeFilterHandlerFactory $defaultAttributeFilterHandlerFactory
     */
    public function __construct(
        RangeFilterHandlerFactory $rangeFilterHandlerFactory,
        CategoryFilterHandlerFactory $categoryFilterHandlerFactory,
        VisibilityFilterHandlerFactory $visibilityFilterHandlerFactory,
        AttributeFilterHandlerFactory $attributeFilterHandlerFactory,
        DefaultAttributeFilterHandlerFactory $defaultAttributeFilterHandlerFactory
    ) {
        $this->rangeFilterHandlerFactory = $rangeFilterHandlerFactory;
        $this->categoryFilterHandlerFactory = $categoryFilterHandlerFactory;
        $this->visibilityFilterHandlerFactory = $visibilityFilterHandlerFactory;
        $this->attributeFilterHandlerFactory = $attributeFilterHandlerFactory;
        $this->defaultAttributeFilterHandlerFactory = $defaultAttributeFilterHandlerFactory;
    }

    /**
     * Resolve handler
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param string $filterKey
     * @param mixed $filterValue
     * @return FilterHandlerInterface
     */
    public function resolve(
        SearchCriteriaInterface $searchCriteria,
        string $filterKey,
        mixed $filterValue
    ): FilterHandlerInterface {
        if (is_array($filterValue) && !empty(array_intersect(array_keys($filterValue), ['from', 'to']))) {
            return $this->rangeFilterHandlerFactory->create(
                ['filterKey' => $filterKey, 'filterValues' => $filterValue]
            );
        }

        return match ($filterKey) {
            'category_ids', 'category_id' => $this->categoryFilterHandlerFactory->create(
                ['filterKey' => $filterKey, 'filterValues' => $filterValue, 'searchCriteria' => $searchCriteria]
            ),
            'visibility' => $this->visibilityFilterHandlerFactory->create(
                ['filterKey' => $filterKey, 'filterValues' => $filterValue]
            ),
            'url_key', 'sku' => $this->defaultAttributeFilterHandlerFactory->create(
                ['filterKey' => $filterKey, 'filterValues' => $filterValue]
            ),
            default => $this->attributeFilterHandlerFactory->create(
                ['filterKey' => $filterKey, 'filterValues' => $filterValue]
            ),
        };
    }
}
