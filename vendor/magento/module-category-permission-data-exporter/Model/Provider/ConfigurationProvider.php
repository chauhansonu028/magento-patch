<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CategoryPermissionDataExporter\Model\Provider;

use Magento\CatalogPermissions\App\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\DataExporter\Model\Logging\CommerceDataExportLoggerInterface as LoggerInterface;
use Magento\QueryXml\Model\QueryProcessor;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Get Global Configuration for Category Permissions.
 * Each permission option is represented as a separate feed item:
 *      {
 *     "id": {
 *       "websiteCode": "FALLBACK_WEBSITE",
 *       "customerGroupCode": "FALLBACK_CUSTOMER_GROUP",
 *       "categoryId": "DISPLAYED"
 *     },
 *     "type": "GLOBAL",
 *     "permission": {
 *       "DISPLAYED": "DENY"
 *     },
 *     "deleted": false
 *   }
 *
 * If Global Configuration was deleted new feed item with {deleted:true} will be returned
 */
class ConfigurationProvider
{

    /**#@+
     * Representation of enum type
     *
     * CategoryPermissionFeed.PermissionKey {
     *   GLOBAL_ENABLED = 0;
     *   DISPLAYED = 1;
     *   DISPLAY_PRODUCT_PRICE = 2;
     *   ADD_TO_CART = 3;
     * }
     * }
     */
    public const PERMISSION_KEY_GLOBAL_ENABLED = 'GLOBAL_ENABLED';
    public const PERMISSION_KEY_DISPLAYED = 'DISPLAYED';
    public const PERMISSION_KEY_DISPLAY_PRODUCT_PRICE = 'DISPLAY_PRODUCT_PRICE';
    public const PERMISSION_KEY_ADD_TO_CART = 'ADD_TO_CART';
    /**#@-*/


    public const CONFIG_PATH_TO_PERMISSION_KEY_MAP = [
        self::CONFIG_PATH_ENABLED => self::PERMISSION_KEY_GLOBAL_ENABLED,
        self::CONFIG_PATH_GRANT_CATALOG_CATEGORY_VIEW => self::PERMISSION_KEY_DISPLAYED,
        self::CONFIG_PATH_GRANT_CATALOG_PRODUCT_PRICE => self::PERMISSION_KEY_DISPLAY_PRODUCT_PRICE,
        self::CONFIG_PATH_GRANT_CHECKOUT_ITEMS => self::PERMISSION_KEY_ADD_TO_CART
    ];

    /**
     * Representation of enum type
     *
     * CategoryPermissionFeed.PermissionFeedType {
     *    STANDARD = 0;
     *    GLOBAL = 1;
     * }
     */
    private const PERMISSION_TYPE_GLOBAL = 'GLOBAL';

    /**#@+
     * System Configuration config path and related values
     */
    private const CONFIG_PATH_ENABLED = 'enabled';
    private const CONFIG_PATH_GRANT_CATALOG_CATEGORY_VIEW = 'grant_catalog_category_view';
    private const CONFIG_PATH_GRANT_CATALOG_CATEGORY_VIEW_GROUPS = 'grant_catalog_category_view_groups';
    private const CONFIG_PATH_GRANT_CATALOG_PRODUCT_PRICE = 'grant_catalog_product_price';
    private const CONFIG_PATH_GRANT_CATALOG_PRODUCT_PRICE_GROUPS = 'grant_catalog_product_price_groups';
    private const CONFIG_PATH_GRANT_CHECKOUT_ITEMS = 'grant_checkout_items';
    private const CONFIG_PATH_GRANT_CHECKOUT_ITEMS_GROUPS = 'grant_checkout_items_groups';

    private const CONFIG_YES_VALUE = 1;

    private const CONFIG_VALUE_ALLOW_FOR_GROUPS = 2;
    private const CONFIG_VALUE_ALLOW = 1;
    private const CONFIG_VALUE_DENY = 0;
    /**#@-*/

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FeedItemBuilder
     */
    private $feedItemBuilder;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param QueryProcessor $queryProcessor
     * @param FeedItemBuilder $feedItemBuilder
     * @param ResourceConnection $resourceConnection
     * @param Config $config
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        FeedItemBuilder $feedItemBuilder,
        ResourceConnection $resourceConnection,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->feedItemBuilder = $feedItemBuilder;
        $this->resourceConnection = $resourceConnection;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Get global configuration
     *
     * @return array
     */
    public function getConfiguration(): array
    {
        $output = [];
        $config = $this->prepareConfig();

        $feedItems = [];
        foreach ($this->getConfigPaths() as $path) {
            if (!isset($config[$path])) {
                $this->logger->error(
                    sprintf(
                        "Catalog Permissions: Global Configuration path was not found. %s",
                        var_export($config, true)
                    )
                );
                continue;
            }

            if ($path == self::CONFIG_PATH_ENABLED) {
                $output[] = $this->feedItemBuilder->buildFeedItem(
                    self::CONFIG_PATH_ENABLED,
                    CategoryPermissions::FALLBACK_WEBSITE,
                    CategoryPermissions::FALLBACK_CUSTOMER_GROUP,
                    self::PERMISSION_TYPE_GLOBAL,
                    null,
                    null,
                    null,
                    $config[$path][CategoryPermissions::FALLBACK_WEBSITE]['value'] == self::CONFIG_YES_VALUE
                        ? CategoryPermissions::PERMISSION_VALUE_ALLOW
                        : CategoryPermissions::PERMISSION_VALUE_DENY,
                    false,
                    CategoryPermissions::GLOBAL_CONFIG_PERMISSION_ID,
                    CategoryPermissions::FALLBACK_WEBSITE,
                    CategoryPermissions::FALLBACK_CUSTOMER_GROUP
                );
                continue;
            }
            $groupsConfigPath = $path . '_groups';

            foreach ($config[$path] as $websiteId => $item) {
                $websiteCode = $item['websiteCode'];
                // resolve customer groups per {GLOBAL_<TYPE>}
                if (
                    $item['value'] == self::CONFIG_VALUE_ALLOW_FOR_GROUPS
                    && !empty($config[$groupsConfigPath][$websiteId])) {
                    $customerGroupIds = explode(',', $config[$groupsConfigPath][$websiteId]['value']);
                    $customerGroupCodes = array_map('sha1', $customerGroupIds);
                    $customerGroupCodes = array_combine($customerGroupIds, $customerGroupCodes);
                } else {
                    $customerGroupCodes = [
                        CategoryPermissions::FALLBACK_CUSTOMER_GROUP => CategoryPermissions::FALLBACK_CUSTOMER_GROUP
                    ];
                }

                $permissionValue = $this->resolvePermissionValue($item['value']);
                if ($permissionValue == null) {
                    $this->logger->error(
                        sprintf(
                            'Catalog Permissions: wrong state in global config. item: %s, config: %s',
                            var_export($item, true),
                            var_export($config, true)
                        )
                    );
                    continue;
                }

                foreach ($customerGroupCodes as $customerGroupId => $customerGroupCode) {
                    $feedItems[$path][$websiteId][$customerGroupId] = $this->feedItemBuilder->buildFeedItem(
                        $path,
                        $websiteCode,
                        $customerGroupCode,
                        self::PERMISSION_TYPE_GLOBAL,
                        self::CONFIG_PATH_GRANT_CATALOG_CATEGORY_VIEW == $path ? $permissionValue : null,
                        self::CONFIG_PATH_GRANT_CATALOG_PRODUCT_PRICE == $path ? $permissionValue : null,
                        self::CONFIG_PATH_GRANT_CHECKOUT_ITEMS == $path ? $permissionValue : null,
                        null,
                        false,
                        CategoryPermissions::GLOBAL_CONFIG_PERMISSION_ID,
                        (string)$websiteId,
                        (string)$customerGroupId
                    );
                }
            }
        }

        // filter feed items if permission on _this_ website level identical to fallback website
        foreach ($feedItems as $itemsPerWebsite) {
            foreach ($itemsPerWebsite as $websiteId => $itemsPerCustomerGroup) {
                foreach ($itemsPerCustomerGroup as $customerGroupId => $item) {
                    $fallbackValue = $itemsPerWebsite[CategoryPermissions::FALLBACK_WEBSITE][$customerGroupId] ?? null;
                    if ($websiteId !== CategoryPermissions::FALLBACK_WEBSITE
                        && $fallbackValue !== null
                        && $fallbackValue['permission'] === $item['permission']) {
                        continue ;
                    }
                    $output[] = $item;
                }
            }
        }

        return array_merge($output, $this->getDeletedFeedItems($output));
    }

    /**
     * @param $rawValue
     * @return string|null
     */
    private function resolvePermissionValue($rawValue): ?string
    {
        return \in_array($rawValue, [self::CONFIG_VALUE_ALLOW_FOR_GROUPS, self::CONFIG_VALUE_ALLOW])
            ? CategoryPermissions::PERMISSION_VALUE_ALLOW
            : ($rawValue == self::CONFIG_VALUE_DENY ? CategoryPermissions::PERMISSION_VALUE_DENY : null);
    }
    /**
     * @return array
     */
    private function prepareConfig(): array
    {
        $prefix = 'catalog/magento_catalogpermissions/';

        $configPathToPermissionTypeValueMap = [
            self::CONFIG_PATH_ENABLED,
            self::CONFIG_PATH_GRANT_CATALOG_CATEGORY_VIEW,
            self::CONFIG_PATH_GRANT_CATALOG_CATEGORY_VIEW_GROUPS,
            self::CONFIG_PATH_GRANT_CATALOG_PRODUCT_PRICE,
            self::CONFIG_PATH_GRANT_CATALOG_PRODUCT_PRICE_GROUPS,
            self::CONFIG_PATH_GRANT_CHECKOUT_ITEMS,
            self::CONFIG_PATH_GRANT_CHECKOUT_ITEMS_GROUPS,
        ];
        $config = [];
        $websites = $this->storeManager->getWebsites();
        foreach ($configPathToPermissionTypeValueMap as $path) {
            $config[$path][CategoryPermissions::FALLBACK_WEBSITE] = [
                'websiteCode' => CategoryPermissions::FALLBACK_WEBSITE,
                'value' => $this->scopeConfig->getValue($prefix . $path)
            ];
            foreach($websites as $website) {
                $websiteId = (string)$website->getId();
                $websiteValue = $this->scopeConfig->getValue(
                    $prefix . $path,
                    ScopeInterface::SCOPE_WEBSITE,
                    $websiteId
                );
                if ($websiteValue === null) {
                    continue ;
                }
                $config[$path][$websiteId] = [
                    'websiteCode' => $website->getCode(),
                    'value' => $websiteValue
                ];
            }
        }

        return $config;
    }

    /**
     * @return string[]
     */
    public function getConfigPaths(): array
    {
        return [self::CONFIG_PATH_ENABLED, self::CONFIG_PATH_GRANT_CATALOG_CATEGORY_VIEW,
            self::CONFIG_PATH_GRANT_CATALOG_PRODUCT_PRICE, self::CONFIG_PATH_GRANT_CHECKOUT_ITEMS];
    }

    /**
     * Prepare feed items that has been deleted from global configuration
     * @param array $newFeedItems
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    private function getDeletedFeedItems(array $newFeedItems): array
    {
        $output = [];
        $connection = $this->resourceConnection->getConnection();

        $keys = array_map(function($feedItem) {
            return $feedItem['id']['categoryId'] . '-' . $feedItem['_website_id'] . '-' . $feedItem['_customer_group_id'];
        }, $newFeedItems);

        $select = $connection->select()
            ->from(
                $this->resourceConnection->getTableName('catalog_data_exporter_category_permissions'),
                [
                    'category_id',
                    'website_id',
                    'customer_group_id',
                    'feed_data',
                    'CONCAT_WS("-", category_id, website_id, customer_group_id) as g'
                ]
            )->where('permission_id = 0')
            ->having('g not in(?)', $keys);
        $cursor = $connection->query($select);
        while ($row = $cursor->fetch()) {
            $feedData = json_decode($row['feed_data'], true);
            $output[] = $this->feedItemBuilder->buildFeedItem(
                $feedData['id']['categoryId'],
                $feedData['id']['websiteCode'],
                $feedData['id']['customerGroupCode'],
                self::PERMISSION_TYPE_GLOBAL,
                null,
                null,
                null,
                null,
                true,
                0,
                (string)$row['website_id'],
                (string)$row['customer_group_id']
            );
        }
        return $output;
    }
}
