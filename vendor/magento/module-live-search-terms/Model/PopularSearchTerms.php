<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LiveSearchTerms\Model;

/**
 * Finds top search results in search
 */
class PopularSearchTerms
{
    /**
     * Check if is cacheable search term
     *
     * @param string $term
     * @param int $storeId
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isCacheable(string $term, int $storeId): bool
    {
        return false;
    }
}
