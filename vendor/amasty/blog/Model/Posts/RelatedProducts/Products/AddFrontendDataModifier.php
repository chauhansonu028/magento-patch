<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Posts\RelatedProducts\Products;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogInventory\Helper\Stock as StockHelper;
use Magento\Store\Model\StoreManagerInterface;

class AddFrontendDataModifier implements CollectionModifierInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StockHelper
     */
    private $stockHelper;

    public function __construct(
        StoreManagerInterface $storeManager,
        StockHelper $stockHelper
    ) {
        $this->storeManager = $storeManager;
        $this->stockHelper = $stockHelper;
    }

    public function modify(ProductCollection $collection): void
    {
        $this->stockHelper->addIsInStockFilterToCollection($collection);
        $currentStore = $this->storeManager->getStore();
        $collection->addAttributeToSelect(['msrp', 'special_price']);
        $collection->addPriceData();
        $collection->addUrlRewrite($currentStore->getRootCategoryId());
        $collection->setStore($currentStore);
    }
}
