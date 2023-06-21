<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model\QueryArgumentProcessor;

use GraphQL\QueryBuilder\QueryBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;

class SearchPhraseQueryArgumentProcessor implements QueryArgumentProcessorInterface
{
    /**
     * @var string
     */
    private const ARGUMENT_NAME = 'phrase';

    /**
     * @var string
     */
    private const TYPE = 'String';

    /**
     * @var bool
     */
    private const IS_REQUIRED = true;

    /**
     * @var string
     */
    private const ARGUMENT_VALUE = '$phrase';

    /**
     * @inheritdoc
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return string
     */
    public function getQueryArgumentValue(SearchCriteriaInterface $searchCriteria): string
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() == 'search_term') {
                    return $filter->getValue();
                }

            }
        }

        return '';
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
