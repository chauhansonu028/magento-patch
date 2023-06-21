<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchStorefrontPopover\Block\Frontend;

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
    private const POPOVER_URL = 'live_search_storefront_popover/frontend_url';

    /**
     * Returns frontend url from config
     *
     * @return string
     */
    public function getPopoverUrl(): string
    {
        return (string) $this->_scopeConfig->getValue(
            self::POPOVER_URL,
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
}
