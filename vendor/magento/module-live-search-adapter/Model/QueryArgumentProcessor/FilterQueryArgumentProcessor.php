<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model\QueryArgumentProcessor;

use GraphQL\QueryBuilder\QueryBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\LiveSearchAdapter\Model\QueryArgumentProcessor\FilterHandler\FilterHandlerFactory;
use Magento\CatalogInventory\Model\Configuration as InventoryConfiguration;

class FilterQueryArgumentProcessor implements QueryArgumentProcessorInterface
{
    /**
     * @var string
     */
    private const ARGUMENT_NAME = 'filter';

    /**
     * @var string
     */
    private const TYPE = '[SearchClauseInput!]';

    /**
     * @var bool
     */
    private const IS_REQUIRED = false;

    /**
     * @var string
     */
    private const ARGUMENT_VALUE = '$filter';

    /**
     * @var FilterHandlerFactory
     */
    private FilterHandlerFactory $filterHandlerFactory;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param FilterHandlerFactory $filterHandlerFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        FilterHandlerFactory $filterHandlerFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->filterHandlerFactory = $filterHandlerFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return array
     */
    public function getQueryArgumentValue(SearchCriteriaInterface $searchCriteria): array
    {
        $filters = $this->parseFilters($searchCriteria);

        $filterVariables = [];
        foreach ($filters as $filterKey => $rawFilterValue) {
            $filterHandler = $this->filterHandlerFactory->resolve($searchCriteria, $filterKey, $rawFilterValue);
            $currentFilterVariables = $filterHandler->getFilterVariables();
            if (!empty($currentFilterVariables)) {
                $filterVariables[] = $currentFilterVariables;
            }

        }

        if (!$this->scopeConfig->getValue(InventoryConfiguration::XML_PATH_SHOW_OUT_OF_STOCK)) {
            $filterVariables[] = [[
                'attribute' => 'inStock',
                'eq' => true
            ]];
        }

        return array_merge([], ...$filterVariables);
    }

    /**
     * Parse filter from search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return array
     */
    private function parseFilters(SearchCriteriaInterface $searchCriteria): array
    {
        $filters = [];
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $field = $filter->getField();
                $condition = $filter->getValue();

                if ($this->stringEndsWith($field, '.from')) {
                    $attribute = substr($field, 0, strpos($field, '.from'));
                    $filters[$attribute]['from'] = $condition;
                } elseif ($this->stringEndsWith($field, '.to')) {
                    $attribute = substr($field, 0, strpos($field, '.to'));
                    $filters[$attribute]['to'] = $condition;
                } elseif ($field !== 'price_dynamic_algorithm' && $field !== 'search_term') {
                    $fieldValue = is_array($condition) ? $condition : [$condition];
                    $filters[$field] = isset($filters[$field])
                        //phpcs:ignore Magento2.Performance.ForeachArrayMerge
                        ? array_merge($filters[$field], $fieldValue)
                        : $fieldValue;
                }
            }
        }
        return $filters;
    }

    /**
     * Format string with specific endings.
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    private function stringEndsWith(string $haystack, string $needle): bool
    {
        return str_ends_with($haystack, $needle);
    }

    /**
     * @inheritdoc
     *
     * @param QueryBuilder $builder
     */
    public function setQueryArguments(QueryBuilder $builder): void
    {
        $builder->setVariable(self::ARGUMENT_NAME, self::TYPE, self::IS_REQUIRED);
        $builder->setArgument(self::ARGUMENT_NAME, self::ARGUMENT_VALUE);
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return self::IS_REQUIRED;
    }
}
