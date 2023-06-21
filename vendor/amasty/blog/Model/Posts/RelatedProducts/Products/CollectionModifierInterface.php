<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Posts\RelatedProducts\Products;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

interface CollectionModifierInterface
{
    public function modify(ProductCollection $collection): void;
}
