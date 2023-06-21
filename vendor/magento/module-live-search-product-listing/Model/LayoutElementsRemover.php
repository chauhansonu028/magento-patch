<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchProductListing\Model;

use Magento\LiveSearchProductListing\Model\Config as ProductListingConfig;
use Magento\Framework\View\Layout\Element;

class LayoutElementsRemover
{
    /**
     * @var ProductListingConfig
     */
    private ProductListingConfig $productListingConfig;

    /**
     * @var string[][]
     */
    private array $elementsToRemoveCache;

    /**
     * @param ProductListingConfig $productListingConfig
     */
    public function __construct(
        ProductListingConfig $productListingConfig
    ) {
        $this->productListingConfig = $productListingConfig;
    }

    /**
     * Removes layout elements if specific configuration enabled in admin.
     *
     * @param Element $currentElement
     * @param string[] $configElementsToRemove
     * @param string $cacheKey
     * @return void
     */
    public function removeLayoutElements(Element $currentElement, array $configElementsToRemove, string $cacheKey): void
    {
        $elementsToRemove = $this->getElementsToRemove($configElementsToRemove, $cacheKey);
        if (\in_array($currentElement->getElementName(), $elementsToRemove)) {
            $currentElement->setAttribute('remove', 'true');
        }
    }

    /**
     * Returns a list of elements in layout that should be removed if a specific configuration is active.
     *
     * @param string[] $configElementsToRemove
     * @param string $cacheKey
     * @return array
     */
    private function getElementsToRemove(array $configElementsToRemove, string $cacheKey): array
    {
        if (!empty($this->elementsToRemoveCache) && !empty($this->elementsToRemoveCache[$cacheKey])) {
            return $this->elementsToRemoveCache[$cacheKey];
        }

        $elementsToRemove = [];
        foreach ($configElementsToRemove as $config => $blocks) {
            if ($this->productListingConfig->isActive($config)) {
                $elementsToRemove[] = $blocks;
            }
        }
        $this->elementsToRemoveCache[$cacheKey] = \array_merge([], ...$elementsToRemove);

        return $this->elementsToRemoveCache[$cacheKey];
    }
}
