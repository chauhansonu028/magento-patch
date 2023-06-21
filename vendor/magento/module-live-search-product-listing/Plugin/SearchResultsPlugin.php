<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchProductListing\Plugin;

use Magento\CatalogSearch\Block\Advanced\Result;
use Magento\LiveSearchProductListing\Model\Config as ProductListingConfig;

class SearchResultsPlugin
{
    /**
     * @var ProductListingConfig
     */
    private ProductListingConfig $productListingConfig;

    /**
     * @var array
     */
    private array $liveSearchConfigurations = [];

    /**
     * @var bool|null
     */
    private ?bool $needToSkip = null;

    /**
     * @param ProductListingConfig $productListingConfig
     * @param array $liveSearchConfigurations
     */
    public function __construct(
        ProductListingConfig $productListingConfig,
        array $liveSearchConfigurations
    ) {
        $this->productListingConfig = $productListingConfig;
        $this->liveSearchConfigurations = $liveSearchConfigurations;
    }

    /**
     * If specific config is enabled, prevent execution of setListOrders method.
     *
     * @param Result $subject
     * @param callable $proceed
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSetListOrders(Result $subject, callable $proceed): void
    {
        if (!$this->needToSkip()) {
            $proceed();
        }
    }

    /**
     * If specific config is enabled, prevent execution of setListModes method.
     *
     * @param Result $subject
     * @param callable $proceed
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSetListModes(Result $subject, callable $proceed): void
    {
        if (!$this->needToSkip()) {
            $proceed();
        }
    }

    /**
     * If specific config is enabled, prevent execution of setListCollection method.
     *
     * @param Result $subject
     * @param callable $proceed
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSetListCollection(Result $subject, callable $proceed): void
    {
        if (!$this->needToSkip()) {
            $proceed();
        }
    }

    /**
     * Check if needed to skip search results methods execution.
     *
     * @return bool
     */
    private function needToSkip(): bool
    {
        if (null !== $this->needToSkip) {
            return $this->needToSkip;
        }
        $this->needToSkip = false;
        foreach ($this->liveSearchConfigurations as $configuration) {
            if ($this->productListingConfig->isActive($configuration)) {
                $this->needToSkip = true;
                break;
            }
        }

        return $this->needToSkip;
    }
}
