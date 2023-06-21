<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model;

use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\Response\AggregationFactory;
use Magento\LiveSearchAdapter\Model\Aggregation\AttributeEmptyBucketHandlerFactory;

class EmptySearchResultBuilder
{
    /**
     * @var SearchResultFactory
     */
    private SearchResultFactory $searchResultFactory;

    /**
     * @var AggregationFactory
     */
    private AggregationFactory $aggregationFactory;

    /**
     * @var FilterableAttributes
     */
    private FilterableAttributes $filterableAttributes;

    /**
     * @var AttributeEmptyBucketHandlerFactory
     */
    private AttributeEmptyBucketHandlerFactory $attributeEmptyBucketHandlerFactory;

    /**
     * @param SearchResultFactory $searchResultFactory
     * @param AggregationFactory $aggregationFactory
     * @param FilterableAttributes $filterableAttributes
     * @param AttributeEmptyBucketHandlerFactory $attributeEmptyBucketHandlerFactory
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        AggregationFactory $aggregationFactory,
        FilterableAttributes $filterableAttributes,
        AttributeEmptyBucketHandlerFactory $attributeEmptyBucketHandlerFactory
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->aggregationFactory = $aggregationFactory;
        $this->filterableAttributes = $filterableAttributes;
        $this->attributeEmptyBucketHandlerFactory = $attributeEmptyBucketHandlerFactory;
    }

    /**
     * Build empty search results.
     *
     * @return SearchResultInterface
     */
    public function build(): SearchResultInterface
    {
        $searchResult = $this->searchResultFactory->create();
        $searchResult->setItems([]);
        $searchResult->setAggregations($this->getAggregations());
        $searchResult->setTotalCount(0);

        return $searchResult;
    }

    /**
     * Returns aggregations for the buckets.
     *
     * @return AggregationInterface
     */
    private function getAggregations(): AggregationInterface
    {
        $emptyBuckets = [];
        $filterableAttributeCodes = $this->filterableAttributes->getMandatoryFilterableAttributesForLayeredNavigation();

        foreach ($filterableAttributeCodes as $attributeCode) {
            $attributeEmptyBucketHandler = $this->attributeEmptyBucketHandlerFactory->create(
                ['attributeCode' => $attributeCode]
            );
            $emptyBuckets[$attributeEmptyBucketHandler->getBucketName()] = $attributeEmptyBucketHandler->getBucket();
        }

        return $this->aggregationFactory->create(['buckets' => $emptyBuckets]);
    }
}
