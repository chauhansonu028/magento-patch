<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\LiveSearchAdapter\Model\ResourceModel;

use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\Catalog\Model\Product\Visibility;

/**
 * Search engine resource model
 */
class Engine implements EngineInterface
{
    /**
     * @var Visibility
     */
    protected Visibility $catalogProductVisibility;

    /**
     * Construct
     *
     * @param Visibility $catalogProductVisibility
     */
    public function __construct(
        Visibility $catalogProductVisibility
    ) {
        $this->catalogProductVisibility = $catalogProductVisibility;
    }

    /**
     * Retrieve allowed visibility values for current engine
     *
     * @return int[]
     */
    public function getAllowedVisibility(): array
    {
        return $this->catalogProductVisibility->getVisibleInSiteIds();
    }

    /**
     * Define if current search engine supports advanced index
     *
     * @return bool
     */
    public function allowAdvancedIndex(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function processAttributeValue($attribute, $value)
    {
        return $value;
    }

    /**
     * Prepare index array as a string glued by separator
     *
     * Support 2 level array gluing
     *
     * @param array $index
     * @param string $separator
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepareEntityIndex($index, $separator = ' '): array
    {
        return $index;
    }

    /**
     * Return if engine available.
     *
     * @return true
     */
    public function isAvailable(): bool
    {
        return true;
    }
}
