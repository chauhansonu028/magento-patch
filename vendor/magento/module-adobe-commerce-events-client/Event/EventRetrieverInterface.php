<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface for retrieving list of events.
 *
 * @api
 * @since 1.1.0
 */
interface EventRetrieverInterface
{
    /**
     * Retrieves a list of the stored events waiting to be sent.
     *
     * @return array
     * @throws LocalizedException
     */
    public function getEvents(): array;
}
