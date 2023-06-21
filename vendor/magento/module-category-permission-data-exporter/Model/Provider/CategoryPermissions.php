<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CategoryPermissionDataExporter\Model\Provider;

use Magento\CatalogPermissions\Model\Permission;
use Magento\Framework\App\ResourceConnection;
use Magento\QueryXml\Model\QueryProcessor;

/**
 * Class ProductOverrides
 */
class CategoryPermissions
{
    public const GLOBAL_CONFIG_PERMISSION_ID = 0;

    public const FALLBACK_WEBSITE = 'FALLBACK_WEBSITE';
    public const FALLBACK_CUSTOMER_GROUP = 'FALLBACK_CUSTOMER_GROUP';

    /**
     * Representation of enum type
     *
     * CategoryPermissionFeed.PermissionFeedType {
     *    STANDARD = 0;
     * }
     */
    private const PERMISSION_TYPE_STANDARD = 'STANDARD';


    /**#@+
     * Representation of enum type
      enum CategoryPermissionFeed.PermissionValue {
        UNDEFINED = 0;
        ALLOW = 1;
        DENY = 2;
        USE_PARENT = 3;
      }
     */
    public const PERMISSION_VALUE_ALLOW = 'ALLOW';
    public const PERMISSION_VALUE_DENY = 'DENY';
    private const PERMISSION_VALUE_USE_PARENT = 'USE_PARENT';
    /**#@-*/

    private const MAGENTO_TO_FEED_STATE_MAPPING = [
        Permission::PERMISSION_ALLOW => self::PERMISSION_VALUE_ALLOW,
        Permission::PERMISSION_DENY => self::PERMISSION_VALUE_DENY,
        Permission::PERMISSION_PARENT => self::PERMISSION_VALUE_USE_PARENT,
    ];

    /**
     * @var QueryProcessor
     */
    private $queryProcessor;

    /**
     * @var FeedItemBuilder
     */
    private $feedItemBuilder;

    /**
     * @var ConfigurationProvider
     */
    private $configurationProvider;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param QueryProcessor $queryProcessor
     * @param ConfigurationProvider $configurationProvider
     * @param FeedItemBuilder $feedItemBuilder
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        QueryProcessor  $queryProcessor,
        ConfigurationProvider $configurationProvider,
        FeedItemBuilder $feedItemBuilder,
        ResourceConnection $resourceConnection
    ) {
        $this->queryProcessor = $queryProcessor;
        $this->feedItemBuilder = $feedItemBuilder;
        $this->configurationProvider = $configurationProvider;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param array $values
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function get(array $values): array
    {
        $output = [];
        $permissionIds = \array_column($values, 'permission_id');

        if ($permissionIds == [self::GLOBAL_CONFIG_PERMISSION_ID]) {
            return $this->configurationProvider->getConfiguration();
        }

        $cursor = $this->queryProcessor->execute('categoryPermissions', ['ids' => $permissionIds]);
        $allowedPermissionValues = array_keys(self::MAGENTO_TO_FEED_STATE_MAPPING);
        while ($row = $cursor->fetch()) {
            $displayable = (int)$row['displayable'];
            $priceDisplayable = (int)$row['priceDisplayable'];
            $addToCart = (int)$row['addToCart'];

            if (!in_array($displayable, $allowedPermissionValues)
                || !in_array($priceDisplayable, $allowedPermissionValues)
                || !in_array($addToCart, $allowedPermissionValues)
            ) {
                continue ;
            }

            $output[] = $this->feedItemBuilder->buildFeedItem(
                $row['categoryId'],
                $row['websiteCode'] ?? self::FALLBACK_WEBSITE,
                $row['customerGroupCode'] ?? self::FALLBACK_CUSTOMER_GROUP,
                self::PERMISSION_TYPE_STANDARD,
                self::MAGENTO_TO_FEED_STATE_MAPPING[$displayable],
                self::MAGENTO_TO_FEED_STATE_MAPPING[$priceDisplayable],
                self::MAGENTO_TO_FEED_STATE_MAPPING[$addToCart],
                null,
                false,
                (int)$row['permission_id'],
                $row['website_id'] ?? self::FALLBACK_WEBSITE,
                $row['customer_group_id'] ?? self::FALLBACK_CUSTOMER_GROUP
            );
        }

        $output = array_merge($output, $this->getDeletedFeedItems($output, $permissionIds));

        return array_merge($output, $this->configurationProvider->getConfiguration());
    }

    /**
     * Prepare feed items that has been deleted from standard configuration
     * @param array $newFeedItems
     * @param array $changedPermissionId
     * @return array
     */
    private function getDeletedFeedItems(array $newFeedItems, array $changedPermissionId): array
    {
        $output = [];
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from(
                $this->resourceConnection->getTableName('catalog_data_exporter_category_permissions'),
                [
                    'CONCAT_WS("-", permission_id, category_id, website_id, customer_group_id) AS g',
                    'category_id',
                    'website_id',
                    'customer_group_id',
                    'feed_data'
                ]
            )
            ->where('permission_id in (?)', $changedPermissionId)
            ->where('is_deleted = 0');

        $newFeeds = array_map(function($feed) {
            return $feed['_permission_id']
                . '-' . $feed['id']['categoryId']
                . '-' . $feed['_website_id']
                . '-' . $feed['_customer_group_id'];
        }, $newFeedItems);

        $existsFeeds = $connection->fetchAssoc($select);
        $deleteFeeds = array_diff_key($existsFeeds, array_flip($newFeeds));

        foreach ($deleteFeeds as $row) {
            $feedData = json_decode($row['feed_data'], true);
            $output[] = $this->feedItemBuilder->buildFeedItem(
                $feedData['id']['categoryId'],
                $feedData['id']['websiteCode'],
                $feedData['id']['customerGroupCode'],
                self::PERMISSION_TYPE_STANDARD,
                null,
                null,
                null,
                null,
                true,
                (int)$feedData['_permission_id'],
                (string)$feedData['_website_id'],
                (string)$feedData['_customer_group_id']
            );
        }
        return $output;
    }
}
