<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ProductOverrideDataExporter\Model;

use Magento\CatalogStaging\Model\Mview\View\Attribute\Subscription;
use Magento\Framework\Mview\ViewInterface;

/**
 * Class Subscription implements statement building for staged entity attribute subscription
 */
class StagingSubscription extends Subscription
{
    /**
     * Build trigger statement for INSERT, UPDATE, DELETE events
     *
     * @param string $event
     * @param ViewInterface $view
     * @return string
     */
    protected function buildStatement(string $event, ViewInterface $view): string
    {
        $result = parent::buildStatement($event, $view);

        $linkId = $this->getColumnName();//entity_id
        $result = preg_replace('/(NEW|OLD)\.`row_id`/', "$1.`$linkId`", $result);
        return $result;
    }
}
