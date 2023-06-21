<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Api\Data;

interface GetRelatedPostsForProductInterface
{
    /**
     * @param int $productId
     * @return PostInterface[]
     */
    public function execute(int $productId): array;
}
