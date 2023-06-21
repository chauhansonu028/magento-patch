<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CategoryPermissionDataExporter\Plugin;

use Magento\CategoryPermissionDataExporter\Model\Provider\CategoryPermissions;
use Magento\DataExporter\Model\Indexer\DataSerializerInterface;
use Magento\DataExporter\Model\Indexer\EntityIdsProviderInterface;
use Magento\DataExporter\Model\Indexer\FeedIndexMetadata;
use Magento\DataExporter\Model\Indexer\FeedIndexProcessorCreateUpdate;
use Magento\DataExporter\Model\Logging\CommerceDataExportLoggerInterface;

/**
 * After full reindex each time reindex global configuration.
 * Covers case when categories do not have permissions, but only global configuration available
 */
class GlobalConfigurationReindex
{
    private CommerceDataExportLoggerInterface $logger;

    /**
     * @param CommerceDataExportLoggerInterface $logger
     */
    public function __construct(
        CommerceDataExportLoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @param FeedIndexProcessorCreateUpdate $subject
     * @param $result
     * @param FeedIndexMetadata $metadata
     * @param DataSerializerInterface $serializer
     * @param EntityIdsProviderInterface $idsProvider
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterFullReindex(
        FeedIndexProcessorCreateUpdate $subject,
        $result,
        FeedIndexMetadata              $metadata,
        DataSerializerInterface        $serializer,
        EntityIdsProviderInterface     $idsProvider
    ): void {
        try {
            if ($metadata->getFeedName() === 'categoryPermissions') {
                $subject->partialReindex(
                    $metadata,
                    $serializer,
                    $idsProvider,
                    [CategoryPermissions::GLOBAL_CONFIG_PERMISSION_ID]
                );
            }
        } catch (\Throwable $e) {
            $this->logger->error(
                'Data Exporter exception has occurred: ' . $e->getMessage(),
                ['exception' => $e]
            );
        }
    }
}
