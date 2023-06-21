<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model\QueryArgumentProcessor\FilterHandler;

class DefaultAttributeFilterHandler implements FilterHandlerInterface
{
    /**
     * @var string
     */
    private string $filterKey;

    /**
     * @var array
     */
    private array $filterValues;

    /**
     * @param string $filterKey
     * @param array $filterValues
     */
    public function __construct(
        string $filterKey,
        array $filterValues
    ) {
        $this->filterKey = $filterKey;
        $this->filterValues = $filterValues;
    }

    /**
     * @inheritdoc
     */
    public function getFilterKey(): string
    {
        return $this->filterKey;
    }

    /**
     * @inheritdoc
     */
    public function getFilterVariables(): array
    {
        if (empty($this->filterValues)) {
            return [];
        }
        return [
            ['attribute' => $this->getFilterKey(), $this->getFilterType() => array_unique($this->filterValues)]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFilterType(): string
    {
        return 'in';
    }
}
