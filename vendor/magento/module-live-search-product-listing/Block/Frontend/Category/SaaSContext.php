<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchProductListing\Block\Frontend\Category;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\LiveSearch\Model\ModuleVersionReader;
use Magento\ServicesId\Model\ServicesConfigInterface;
use Magento\Customer\Model\Session;

/**
 * SaasContext block for Category.
 *
 * @api
 */
class SaaSContext extends \Magento\LiveSearchProductListing\Block\Frontend\SaaSContext
{
    /**
     * @var Resolver
     */
    private Resolver $layerResolver;

    /**
     * @param Context $context
     * @param ServicesConfigInterface $servicesConfig
     * @param ProductMetadata $productMetadata
     * @param ModuleVersionReader $moduleVersionReader
     * @param CurrencyInterface $localeCurrency
     * @param Session $customerSession
     * @param Resolver $layerResolver
     */
    public function __construct(
        Context $context,
        ServicesConfigInterface $servicesConfig,
        ProductMetadata $productMetadata,
        ModuleVersionReader $moduleVersionReader,
        CurrencyInterface $localeCurrency,
        Session $customerSession,
        Resolver $layerResolver
    ) {
        $this->layerResolver = $layerResolver;
        parent::__construct(
            $context,
            $servicesConfig,
            $productMetadata,
            $moduleVersionReader,
            $localeCurrency,
            $customerSession
        );
    }

    /**
     * Returns current category id
     *
     * @return int
     */
    public function getCategoryId(): int
    {
        return (int)$this->layerResolver->get()->getCurrentCategory()->getId();
    }

    /**
     * Getting category url path
     *
     * @return string
     */
    public function getCategoryUrlPath(): string
    {
        /** @var  \Magento\Catalog\Model\Category $currentCategory */
        $currentCategory = $this->layerResolver->get()->getCurrentCategory();

        return (string)$currentCategory->getUrlPath();
    }

    /**
     * Getting category name
     *
     * @return string
     */
    public function getCategoryName(): string
    {
        /** @var  \Magento\Catalog\Model\Category $currentCategory */
        $currentCategory = $this->layerResolver->get()->getCurrentCategory();

        return $currentCategory->getName();
    }

    /**
     * Getting category display mode
     *
     * @return string
     */
    public function getCategoryDisplayMode(): string
    {
        /** @var  \Magento\Catalog\Model\Category $currentCategory */
        $currentCategory = $this->layerResolver->get()->getCurrentCategory();

        return (string)$currentCategory->getDisplayMode();
    }
}
