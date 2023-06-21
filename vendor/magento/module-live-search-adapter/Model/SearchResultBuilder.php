<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\DocumentFactory;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Search\Response\AggregationFactory;
use Magento\LiveSearchAdapter\Model\Aggregation\AttributeEmptyBucketHandlerFactory;
use Magento\LiveSearchAdapter\Model\Aggregation\BucketHandlerFactory;

/**
 * Builder for search results.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SearchResultBuilder
{
    /**
     * @var SearchResultFactory
     */
    private SearchResultFactory $searchResultFactory;

    /**
     * @var DocumentFactory
     */
    private DocumentFactory $documentFactory;

    /**
     * @var BucketHandlerFactory
     */
    private BucketHandlerFactory $bucketHandlerFactory;

    /**
     * @var AggregationFactory
     */
    private AggregationFactory $aggregationFactory;

    /**
     * @var SearchScopeResolver
     */
    private SearchScopeResolver $scopeResolver;

    /**
     * @var AttributeMetadata
     */
    private AttributeMetadata $attributeMetadata;

    /**
     * @var AttributeValueFactory
     */
    private AttributeValueFactory $attributeValueFactory;

    /**
     * @var FilterableAttributes
     */
    private FilterableAttributes $filterableAttributes;

    /**
     * @var AttributeEmptyBucketHandlerFactory
     */
    private AttributeEmptyBucketHandlerFactory $attributeEmptyBucketHandlerFactory;

    /**
     * @var EmptySearchResultBuilder
     */
    private EmptySearchResultBuilder $emptySearchResultBuilder;

    /**
     * @param SearchResultFactory $searchResultFactory
     * @param DocumentFactory $documentFactory
     * @param BucketHandlerFactory $bucketHandlerFactory
     * @param AggregationFactory $aggregationFactory
     * @param SearchScopeResolver $scopeResolver
     * @param AttributeMetadata $attributeMetadata
     * @param AttributeValueFactory $attributeValueFactory
     * @param FilterableAttributes $filterableAttributes
     * @param AttributeEmptyBucketHandlerFactory $attributeEmptyBucketHandlerFactory
     * @param EmptySearchResultBuilder $emptySearchResultBuilder
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        DocumentFactory $documentFactory,
        BucketHandlerFactory $bucketHandlerFactory,
        AggregationFactory $aggregationFactory,
        SearchScopeResolver $scopeResolver,
        AttributeMetadata $attributeMetadata,
        AttributeValueFactory $attributeValueFactory,
        FilterableAttributes $filterableAttributes,
        AttributeEmptyBucketHandlerFactory $attributeEmptyBucketHandlerFactory,
        EmptySearchResultBuilder $emptySearchResultBuilder
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->documentFactory = $documentFactory;
        $this->bucketHandlerFactory = $bucketHandlerFactory;
        $this->aggregationFactory = $aggregationFactory;
        $this->scopeResolver = $scopeResolver;
        $this->attributeMetadata = $attributeMetadata;
        $this->attributeValueFactory = $attributeValueFactory;
        $this->filterableAttributes = $filterableAttributes;
        $this->attributeEmptyBucketHandlerFactory = $attributeEmptyBucketHandlerFactory;
        $this->emptySearchResultBuilder = $emptySearchResultBuilder;
    }

    /**
     * Build search result.
     *
     * @param array $saasResult
     * @return SearchResultInterface
     * @throws NoSuchEntityException
     */
    public function build(array $saasResult): SearchResultInterface
    {
        if (!isset($saasResult['data'])) {
            return $this->emptySearchResultBuilder->build();
        }
        $searchResult = $this->searchResultFactory->create();
        $searchData = $saasResult['data'];
        $items = $this->getItems($searchData['productSearch']);
        $searchResult->setItems($items);
        $aggregations = $this->getAggregations($searchData['productSearch']);
        $searchResult->setAggregations($aggregations);
        $searchResult->setTotalCount($this->getTotalCount($searchData, $items));

        return $searchResult;
    }

    /**
     * Get search result items.
     *
     * @param array $searchData
     *
     * @return array
     */
    private function getItems(array $searchData): array
    {
        $items = [];
        if (isset($searchData['items']) && is_array($searchData['items'])) {
            $score = count($searchData['items']);
            $productIds = [];
            foreach ($searchData['items'] as $item) {
                // filter out duplicates that the backend might have due to sku editing
                if (!in_array($item['product']['uid'], $productIds, true)) {
                    $attributeScore = $this->attributeValueFactory->create();
                    $attributeScore->setAttributeCode('score');
                    $attributeScore->setValue($score);
                    $document = $this->documentFactory->create();
                    $document->setId((int)$item['product']['uid']);
                    $document->setCustomAttributes(['score' => $attributeScore]);
                    $items[] = $document;
                    --$score;
                    $productIds[] = $item['product']['uid'];
                }
            }
        }

        return $items;
    }

    /**
     * Get aggregations for seearch data.
     *
     * @param array $searchData
     *
     * @return AggregationInterface
     * @throws NoSuchEntityException
     */
    private function getAggregations(array $searchData): AggregationInterface
    {
        $buckets = [];
        $searchApiAttributeCodes = [];
        if (isset($searchData['facets'])) {
            $attributeCodes =  array_column($searchData['facets'], 'attribute');
            // category and price are special cases and not standard attributes
            $attributeCodes = array_diff($attributeCodes, ['categories', 'price']);
            $storeViewCode = $this->scopeResolver->getStoreViewCode();
            $attributesMetadata = $this->attributeMetadata->getAttributesMetadata($attributeCodes);

            foreach ($searchData['facets'] as $facet) {
                $bucketHandler = $this->bucketHandlerFactory->resolve(
                    $facet['attribute'],
                    $facet['buckets'],
                    $attributesMetadata,
                    $storeViewCode
                );
                $bucket = $bucketHandler->getBucket();
                if (!empty($bucket)) {
                    $buckets[$bucketHandler->getBucketName()] = $bucket;
                    $searchApiAttributeCodes[] = $facet['attribute'];
                }
            }
        }

        // create empty buckets for filterable attributes in Magento
        // that are not present in SaaS search results as a
        // workaround to avoid exception in
        // Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection::getFacetedData()
        $emptyBuckets = $this->createEmptyBucketsForMandatoryFilterableAttributes($searchApiAttributeCodes);

        return $this->aggregationFactory->create([
            'buckets' => array_merge($emptyBuckets, $buckets)
        ]);
    }

    /**
     * Returns empty buckets for mandatory attributes.
     *
     * @param array $searchApiAttributeCodes
     * @return array
     */
    private function createEmptyBucketsForMandatoryFilterableAttributes(array $searchApiAttributeCodes): array
    {
        $buckets = [];
        $filterableAttributeCodes = $this->filterableAttributes->getMandatoryFilterableAttributesForLayeredNavigation();
        $filterableAttributeCodes = array_diff($filterableAttributeCodes, $searchApiAttributeCodes);

        foreach ($filterableAttributeCodes as $attributeCode) {
            $attributeEmptyBucketHandler = $this->attributeEmptyBucketHandlerFactory->create(
                ['attributeCode' => $attributeCode]
            );
            $buckets[$attributeEmptyBucketHandler->getBucketName()] = $attributeEmptyBucketHandler->getBucket();
        }

        return $buckets;
    }

    /**
     * Get total count for search results.
     *
     * @param array $searchData
     * @param array $uniqueProducts
     * @return int
     */
    private function getTotalCount(array $searchData, array $uniqueProducts): int
    {
        $totalCount = 0;
        if (!empty($uniqueProducts) && $searchData['productSearch']['total_count'] > 0) {
            $totalCount = $searchData['productSearch']['total_count'];
            $currentPageProductCount = count($uniqueProducts);
            $currentPageUniqueProductCount = count($searchData['productSearch']['items']);
            if ($currentPageProductCount > $currentPageUniqueProductCount) {
                $totalCount -= ($currentPageProductCount - $currentPageUniqueProductCount);
            }
        }
        return $totalCount;
    }
}
