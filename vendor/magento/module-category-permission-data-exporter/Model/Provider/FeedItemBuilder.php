<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CategoryPermissionDataExporter\Model\Provider;

/**
 * Build feed item for Category Permissions feed
 */
class FeedItemBuilder
{
    /**
     * @param string $categoryId
     * @param string $websiteCode
     * @param string $customerGroupCode
     * @param string $type
     * @param string|null $displayable
     * @param string|null $priceDisplayable
     * @param string|null $addToCart
     * @param string|null $enabled
     * @param bool $deleted
     * @param int|null $identifier
     * @param string|null $websiteId
     * @param string|null $customerGroupId
     * @return array
     */
    public function buildFeedItem(
        string $categoryId,
        string $websiteCode,
        string $customerGroupCode,
        string $type,
        ?string $displayable = null,
        ?string $priceDisplayable = null,
        ?string $addToCart = null,
        ?string $enabled = null,
        bool $deleted = false,
        int $identifier = null,
        string $websiteId = null,
        string $customerGroupId = null
    ): array
    {
        $categoryId = ConfigurationProvider::CONFIG_PATH_TO_PERMISSION_KEY_MAP[$categoryId] ?? $categoryId;

        $permissions = [];
        if ($displayable != null) {
            $permissions[ConfigurationProvider::PERMISSION_KEY_DISPLAYED] = $displayable;
        }
        if ($addToCart != null) {
            $permissions[ConfigurationProvider::PERMISSION_KEY_ADD_TO_CART] = $addToCart;
        }
        if ($priceDisplayable != null) {
            $permissions[ConfigurationProvider::PERMISSION_KEY_DISPLAY_PRODUCT_PRICE] = $priceDisplayable;
        }
        if ($enabled != null) {
            $permissions[ConfigurationProvider::PERMISSION_KEY_GLOBAL_ENABLED] = $enabled;
        }

        return [
            '_permission_id' => $identifier,
            '_website_id' => $websiteId,
            '_customer_group_id' => $customerGroupId,
            'id' => [
                'websiteCode' => $websiteCode,
                'customerGroupCode' => $customerGroupCode,
                'categoryId' => $categoryId,
            ],
            'type' => $type,
            'permission' => $permissions,
            'deleted' => $deleted
        ];
    }
}
