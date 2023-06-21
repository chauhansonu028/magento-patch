<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LiveSearchAdapter\Model\Indexer;

use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Indexer\DimensionalIndexerInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Traversable;

/**
 * Dummy Indexer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 100.0.2
 */
class Fulltext implements
    ActionInterface,
    MviewActionInterface,
    DimensionalIndexerInterface
{
    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($ids): void //phpcs:ignore
    {
    }

    /**
     * @inheritdoc
     *
     * @param array $dimensions
     * @param Traversable|null $entityIds
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function executeByDimensions(array $dimensions, Traversable $entityIds = null): void //phpcs:ignore
    {
    }

    /**
     * @inheritdoc
     *
     * @return void
     */
    public function executeFull(): void //phpcs:ignore
    {
    }

    /**
     * @inheritdoc
     *
     * @param int[] $ids
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function executeList(array $ids): void //phpcs:ignore
    {
    }

    /**
     * @inheritdoc
     *
     * @param int $id
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function executeRow($id): void //phpcs:ignore
    {
    }
}
