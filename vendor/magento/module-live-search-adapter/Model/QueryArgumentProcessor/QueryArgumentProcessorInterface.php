<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model\QueryArgumentProcessor;

use GraphQL\QueryBuilder\QueryBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;

interface QueryArgumentProcessorInterface
{
    /**
     * Get current page instance from given search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getQueryArgumentValue(SearchCriteriaInterface $searchCriteria);

    /**
     * Set query variables and arguments to QueryBuilder.
     *
     * @param QueryBuilder $builder
     */
    public function setQueryArguments(QueryBuilder $builder): void;

    /**
     * Return bool if queryArgumentProcessor is required.
     *
     * @return bool
     */
    public function isRequired(): bool;
}
