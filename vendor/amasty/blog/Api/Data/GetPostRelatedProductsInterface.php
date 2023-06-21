<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Api\Data;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * @api
 */
interface GetPostRelatedProductsInterface
{
    /**
     * @param int $postId
     * @return ProductInterface[]
     */
    public function execute(int $postId): array;
}
