<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model\QueryArgumentProcessor\FilterHandler;

use Magento\Catalog\Model\Product\Visibility;

class VisibilityFilterHandler implements FilterHandlerInterface
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
        $visibilityIds = array_unique($this->filterValues);
        $visibilityText = [];
        foreach ($visibilityIds as $visibilityId) {
            $visibilityText[] = Visibility::getOptionText((int)$visibilityId)->getText();
        }

        if (empty($visibilityText)) {
            return [];
        }

        return [
            ['attribute' => $this->getFilterKey(), $this->getFilterType() => $visibilityText]
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
