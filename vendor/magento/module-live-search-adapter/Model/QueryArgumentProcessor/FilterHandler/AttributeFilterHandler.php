<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model\QueryArgumentProcessor\FilterHandler;

use Magento\LiveSearchAdapter\Model\AttributeMetadata;

class AttributeFilterHandler implements FilterHandlerInterface
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
     * @var AttributeMetadata
     */
    private AttributeMetadata $attributeMetadata;

    /**
     * @param string $filterKey
     * @param array $filterValues
     * @param AttributeMetadata $attributeMetadata
     */
    public function __construct(
        string $filterKey,
        array $filterValues,
        AttributeMetadata $attributeMetadata
    ) {
        $this->filterKey = $filterKey;
        $this->filterValues = $filterValues;
        $this->attributeMetadata = $attributeMetadata;
    }

    /**
     * @inheritdoc
     */
    public function getFilterKey(): string
    {
        return $this->filterKey;
    }

    /**
     * Get filter values
     *
     * @return array
     */
    public function getFilterVariables(): array
    {
        $optionIds = array_unique($this->filterValues);
        $optionTexts = [];
        foreach ($optionIds as $optionId) {
            $optionText = $this->attributeMetadata->getOptionById($this->filterKey, (int)$optionId);
            if (!empty($optionText)) {
                $optionTexts[] = $optionText;
            }
        }

        if (empty($optionTexts)) {
            return [];
        }

        return [
            ['attribute' => $this->getFilterKey(), $this->getFilterType() => $optionTexts]
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
