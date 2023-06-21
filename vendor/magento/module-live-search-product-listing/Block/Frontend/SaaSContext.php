<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchProductListing\Block\Frontend;

use Magento\LiveSearch\Block\BaseSaaSContext;
use Magento\Store\Model\ScopeInterface;
use Magento\CatalogInventory\Model\Configuration as InventoryConfiguration;

/**
 * @api
 */
class SaaSContext extends BaseSaaSContext
{
    /**
     * Config path to frontend url
     *
     * @var string
     */
    private const FRONTEND_URL = 'live_search_product_listing/frontend_url';

    /**
     * Autocomplete limit
     *
     * @var string
     */
    private const AUTOCOMPLETE_LIMIT = 'catalog/search/autocomplete_limit';

    /**
     * Returns config for frontend url
     *
     * @return string
     */
    public function getFrontendUrl(): string
    {
        return (string) $this->_scopeConfig->getValue(
            self::FRONTEND_URL,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Returns display out of stock from config
     *
     * @return bool
     */
    public function isDisplayOutOfStock(): bool
    {
        return (bool) $this->_scopeConfig->getValue(InventoryConfiguration::XML_PATH_SHOW_OUT_OF_STOCK);
    }

    /**
     * Returns autocomplete limit from config
     *
     * @return int
     */
    public function getAutocompleteLimit(): int
    {
        return (int) $this->_scopeConfig->getValue(
            self::AUTOCOMPLETE_LIMIT,
            ScopeInterface::SCOPE_STORES
        );
    }
}
