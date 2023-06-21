<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductOverrideDataExporter\Model\Indexer;

/**
 * Responsible to reindex price feed when new version of entity is updated to handle Staging feature
 */
class PriceIndexerProcessor extends \Magento\Framework\Indexer\AbstractProcessor
{
    /**
     * Get Indexer ID for catalog_data_exporter_product_prices
     */
    public const INDEXER_ID = 'catalog_data_exporter_product_prices';
}
