<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model\QueryArgumentProcessor;

use GraphQL\QueryBuilder\QueryBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;

class SortQueryArgumentProcessor implements QueryArgumentProcessorInterface
{
    /**
     * @var string
     */
    private const ARGUMENT_NAME = 'sort';

    /**
     * @var string
     */
    private const TYPE = '[ProductSearchSortInput!]';

    /**
     * @var bool
     */
    private const IS_REQUIRED = false;

    /**
     * @var string
     */
    private const ARGUMENT_VALUE = '$sort';

    /**
     * @var array
     */
    private const UNSUPPORTED_SORT_FIELDS = ['entity_id', '_id'];

    /**
     * @inheritdoc
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return array
     */
    public function getQueryArgumentValue(SearchCriteriaInterface $searchCriteria): array
    {
        if (empty($searchCriteria->getSortOrders())) {
            return [];
        }
        $sortVariables = [];
        foreach ($searchCriteria->getSortOrders() as $sortKey => $sortOrder) {
            /**
             * Sort order array has an attribute like name / price / relevance and entity_id (or _id).
             * entity_id is used in addition to the primary sort attribute in case of a tie.
             * We are ignoring sorting by entity_id and _id here.
             */
            if ($sortOrder instanceof SortOrder) {
                $sortKey = $sortOrder->getField();
                $sortOrder = $sortOrder->getDirection();

            }
            $sortVariable = $this->createSortVariable((string)$sortKey, (string)$sortOrder);

            if (!empty($sortVariable)) {
                $sortVariables[] = $sortVariable;
            }
        }

        return $sortVariables;
    }

    /**
     * Create sort variable
     *
     * @param string $sortField
     * @param string $sortDirection
     * @return array
     */
    private function createSortVariable(string $sortField, string $sortDirection): array
    {
        $sortVariable = [];
        if (!empty($sortField) && !in_array($sortField, self::UNSUPPORTED_SORT_FIELDS, true)) {
            $sortVariable = [
                'attribute' => $sortField,
                'direction' => strtoupper($sortDirection) === SortOrder::SORT_ASC
                    ? SortOrder::SORT_ASC
                    : SortOrder::SORT_DESC
            ];
        }

        return $sortVariable;
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
