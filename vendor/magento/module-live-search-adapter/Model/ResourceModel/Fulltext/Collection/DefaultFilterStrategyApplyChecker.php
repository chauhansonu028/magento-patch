<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model\ResourceModel\Fulltext\Collection;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\DefaultFilterStrategyApplyCheckerInterface;

class DefaultFilterStrategyApplyChecker implements DefaultFilterStrategyApplyCheckerInterface
{
    /**
     * Check if strategy is applicable for current engine.
     *
     * @return bool
     */
    public function isApplicable(): bool
    {
        return false;
    }
}
