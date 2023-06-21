<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CategoryPermissionDataExporter\Model;

use Magento\DataExporter\Model\Indexer\FeedIndexMetadata;
use Magento\DataExporter\Model\Indexer\MarkRemovedEntitiesInterface;

/**
 * Disable built-in mechanism in favor of custom implementation
 * Covers case with updating scope (website or customer group) for the same permission id to send "deleted" event
 * @see \Magento\CategoryPermissionDataExporter\Model\Provider\CategoryPermissions::getDeletedFeedItems
 */
class MarkRemovedEntities implements MarkRemovedEntitiesInterface
{
    public function execute(array $ids, FeedIndexMetadata $metadata): void {}
}