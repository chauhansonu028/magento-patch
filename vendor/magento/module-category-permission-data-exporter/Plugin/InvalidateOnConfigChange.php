<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CategoryPermissionDataExporter\Plugin;

use Magento\CategoryPermissionDataExporter\Model\Provider\CategoryPermissions;
use Magento\Config\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Indexer\Model\IndexerFactory;
use Magento\DataExporter\Model\Logging\CommerceDataExportLoggerInterface;

/**
 * Invalidates indexes if specific configuration value has been changed
 */
class InvalidateOnConfigChange
{
    /**
     * @var array Tmp storage for indexer configuration
     */
    private array $config = [];

    private ScopeConfigInterface $scopeConfig;
    private IndexerFactory $indexerFactory;
    private CommerceDataExportLoggerInterface $logger;
    private ResourceConnection $resourceConnection;
    private IndexerRegistry $indexerRegistry;

    /**
     * @param IndexerFactory $indexerFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param CommerceDataExportLoggerInterface $logger
     * @param ResourceConnection $resourceConnection
     * @param IndexerRegistry $indexerRegistry
     * @param array $indexers
     */
    public function __construct(
        IndexerFactory                    $indexerFactory,
        ScopeConfigInterface              $scopeConfig,
        CommerceDataExportLoggerInterface $logger,
        ResourceConnection                $resourceConnection,
        IndexerRegistry                   $indexerRegistry,
        array                             $indexers = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->scopeConfig = $scopeConfig;
        $this->indexerFactory = $indexerFactory;
        $this->logger = $logger;

        foreach ($indexers as $indexer => $configPerIndexer) {
            foreach ($configPerIndexer as $configPath) {
                if (!isset($this->config[$configPath])) {
                    $this->config[$configPath] = [];
                }
                $this->config[$configPath][$indexer] = $indexer;
            }
        }
        $this->resourceConnection = $resourceConnection;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Invalidate indexer if relevant config value is changed
     *
     * @param Config $subject
     * @return Config
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(Config $subject)
    {
        try {
            $categoryFeedIndexer = $this->indexerRegistry->get('catalog_data_exporter_category_permissions');
            if (!$categoryFeedIndexer->isScheduled()) {
                return null;
            }
            $savedSection = $subject->getSection();
            $configPath = \array_keys($this->config);
            foreach ($configPath as $searchValue) {
                $path = explode('/', $searchValue);
                [$section, $group, $field] = $path;
                if (($savedSection === $section) && isset($subject['groups'][$group]['fields'][$field])) {
                    $savedField = $subject['groups'][$group]['fields'][$field];
                    $beforeValue = $this->scopeConfig->getValue($searchValue);
                    $afterValue = $savedField['value'] ?? $savedField['inherit'] ?? null;
                    if (is_array($afterValue)) {
                        $afterValue = implode(',', $afterValue);
                    }
                    if ($beforeValue !== $afterValue) {
                        $indexers[] = $this->config[$searchValue];
                    }
                }
            }

            if (isset($indexers)) {
                $indexers = array_unique(array_merge(...$indexers));
                $this->updateChangelog($indexers);
            }

        } catch (\Throwable $e) {
            $this->logger->error('Cannot invalidate indexer during system configuration save: ' . $e->getMessage());
        }

        return null;
    }

    private function updateChangelog($indexers): void
    {
        $connection = $this->resourceConnection->getConnection();
        $view = $this->indexerFactory->create()->load(array_key_first($indexers))->getView();
        $tableName = $view->getChangelog()->getName();
        $realTableName = $this->resourceConnection->getTableName($tableName);
        // add "GLOBAL_CONFIG_PERMISSION_ID" id into changelog table in order to collect system configuration updates.
        $connection->insertArray($realTableName, ['entity_id'], [CategoryPermissions::GLOBAL_CONFIG_PERMISSION_ID]);
    }
}
