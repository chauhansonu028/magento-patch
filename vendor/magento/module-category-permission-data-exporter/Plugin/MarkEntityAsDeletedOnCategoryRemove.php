<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CategoryPermissionDataExporter\Plugin;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\DataExporter\Model\Logging\CommerceDataExportLoggerInterface as LoggerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexerRegistry;

/**
 * Plugin for category resource model
 */
class MarkEntityAsDeletedOnCategoryRemove
{
    private ResourceConnection $resourceConnection;

    private IndexerRegistry $indexerRegistry;

    private LoggerInterface $logger;

    /**
     * @param ResourceConnection $resourceConnection
     * @param IndexerRegistry $indexerRegistry
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        IndexerRegistry $indexerRegistry,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->indexerRegistry = $indexerRegistry;
        $this->logger = $logger;
    }

    /**
     * Add permission id of removed category to catalog_data_exporter_category_permissions change log
     *
     * @param Category $subject
     * @param CategoryInterface $category
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDelete(Category $subject, CategoryInterface $category): void
    {
        try {
            $indexer = $this->indexerRegistry->get('catalog_data_exporter_category_permissions');
            if (!$indexer->isScheduled()) {
                return;
            }

            $connection = $this->resourceConnection->getConnection('indexer');
            $query = $connection->select()
                ->from(
                    ['permissions' => $this->resourceConnection->getTableName('magento_catalogpermissions')],
                    ['permission_id']
                )
                ->where('permissions.category_id=?', $category->getId());

            $connection->query($query->insertFromSelect(
                $this->resourceConnection->getTableName($indexer->getView()->getChangelog()->getName()),
                ['entity_id']
            ));
        } catch (\Throwable $e) {
            $this->logger->error(
                'Can\'t update category permissions' . $e->getMessage(),
                ['exception' => $e]
            );
        }
    }
}
