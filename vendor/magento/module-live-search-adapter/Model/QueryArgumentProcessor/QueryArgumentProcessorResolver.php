<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model\QueryArgumentProcessor;

use GraphQL\Exception\ArgumentException;

/**
 * Repository for getting queryArgumentProcessors
 */
class QueryArgumentProcessorResolver
{
    /**
     * @var QueryArgumentProcessorInterface[]
     */
    private array $queryArgumentProcessors;

    /**
     * @param QueryArgumentProcessorInterface[] $queryArgumentProcessors
     */
    public function __construct(
        array $queryArgumentProcessors = []
    ) {
        $this->queryArgumentProcessors = $queryArgumentProcessors;
    }

    /**
     * Resolve and return Query Argument Processor by queryArgumentName
     *
     * @param string $queryArgumentName
     * @return QueryArgumentProcessorInterface
     * @throws ArgumentException
     */
    public function resolve(string $queryArgumentName): QueryArgumentProcessorInterface
    {
        if (!isset($this->queryArgumentProcessors[$queryArgumentName])) {
            throw new ArgumentException(
                sprintf(
                    'Failed to find processor for query argument  "%s"',
                    $queryArgumentName
                )
            );
        }
        return $this->queryArgumentProcessors[$queryArgumentName];
    }
}
