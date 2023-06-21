<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model\QueryArgumentProcessor;

use GraphQL\QueryBuilder\QueryBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\LiveSearch\Block\BaseSaaSContext;

/**
 * QueryArgumentProcessor for Context argument
 */
class ContextQueryArgumentProcessor implements QueryArgumentProcessorInterface
{
    /**
     * @var string
     */
    private const ARGUMENT_NAME = 'context';

    /**
     * @var string
     */
    private const TYPE = 'QueryContextInput!';

    /**
     * @var bool
     */
    private const IS_REQUIRED = false;

    /**
     * @var string
     */
    private const ARGUMENT_VALUE = '$context';

    /**
     * @var BaseSaaSContext
     */
    private BaseSaaSContext $baseSaaSContext;

    /**
     * @param BaseSaaSContext $baseSaaSContext
     */
    public function __construct(
        BaseSaaSContext $baseSaaSContext
    ) {
        $this->baseSaaSContext = $baseSaaSContext;
    }

    /**
     * @inheritdoc
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return string[]
     */
    public function getQueryArgumentValue(SearchCriteriaInterface $searchCriteria): array
    {
        return [
            'customerGroup' => $this->getCustomerGroupCode()
        ];
    }

    /**
     * Get customer group code.
     *
     * @return string
     */
    private function getCustomerGroupCode(): string
    {
        return $this->baseSaaSContext->getCustomerGroupCode();
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
