<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Search\Api\SearchInterface;
use Psr\Log\LoggerInterface;

/**
 * Class SearchAdapter
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * Overloads control under search requests
 */
class SearchAdapter implements SearchInterface
{
    /**
     * @var QueryBuilder
     */
    private QueryBuilder $queryBuilder;

    /**
     * @var SearchClient
     */
    private SearchClient $searchClient;

    /**
     * @var SearchResultBuilder
     */
    private SearchResultBuilder $searchResultBuilder;

    /**
     * @var EmptySearchResultBuilder
     */
    private EmptySearchResultBuilder $emptySearchResultBuilder;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var SearchResultInterface|null
     */
    private ?SearchResultInterface $searchResults = null;

    /**
     * @var \Exception|null
     */
    private ?\Exception $searchException;

    /**
     * @param QueryBuilder $queryBuilder
     * @param SearchClient $searchClient
     * @param SearchResultBuilder $searchResultBuilder
     * @param EmptySearchResultBuilder $emptySearchResultBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        QueryBuilder $queryBuilder,
        SearchClient $searchClient,
        SearchResultBuilder $searchResultBuilder,
        EmptySearchResultBuilder $emptySearchResultBuilder,
        LoggerInterface $logger
    ) {
        $this->queryBuilder = $queryBuilder;
        $this->searchClient = $searchClient;
        $this->searchResultBuilder = $searchResultBuilder;
        $this->emptySearchResultBuilder = $emptySearchResultBuilder;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultInterface
     */
    public function search(SearchCriteriaInterface $searchCriteria): SearchResultInterface
    {
        $this->searchException = null;
        try {
            if (empty($searchCriteria->getSortOrders())) {
                $searchCriteria->setSortOrders([]);
            }

            $body = $this->queryBuilder->build($searchCriteria);
            $saasResult = $this->searchClient->request($body);
            $this->searchResults = $this->searchResultBuilder->build($saasResult);
        } catch (\Exception $exception) {
            $this->logger->error('LiveSearchAdapter: An error occurred: ' . $exception->getMessage());
            $this->searchResults = $this->emptySearchResultBuilder->build();
            $this->searchException = $exception;
        }

        $this->searchResults->setSearchCriteria($searchCriteria);

        return $this->searchResults;
    }

    /**
     * Returns the last search results.
     *
     * @return SearchResultInterface|null
     */
    public function getSearchResults(): ?SearchResultInterface
    {
        return $this->searchResults;
    }

    /**
     * Returns exception from the last search call.
     *
     * @return \Exception|null
     */
    public function getSearchException(): ?\Exception
    {
        return $this->searchException;
    }
}
