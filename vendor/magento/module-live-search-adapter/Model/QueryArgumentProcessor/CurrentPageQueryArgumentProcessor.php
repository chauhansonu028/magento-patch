<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model\QueryArgumentProcessor;

use GraphQL\QueryBuilder\QueryBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Gets query arguments for current page.
 */
class CurrentPageQueryArgumentProcessor implements QueryArgumentProcessorInterface
{
    /**
     * @var string
     */
    private const ARGUMENT_NAME = 'current_page';

    /**
     * @var string
     */
    private const TYPE = 'Int';

    /**
     * @var bool
     */
    private const IS_REQUIRED = false;

    /**
     * @var string
     */
    private const ARGUMENT_VALUE = '$current_page';

    /**
     * @inheritdoc
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return int|null
     */
    public function getQueryArgumentValue(SearchCriteriaInterface $searchCriteria): ?int
    {
        if ($searchCriteria->getCurrentPage()) {
            return $searchCriteria->getCurrentPage();
        }

        return null;
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
