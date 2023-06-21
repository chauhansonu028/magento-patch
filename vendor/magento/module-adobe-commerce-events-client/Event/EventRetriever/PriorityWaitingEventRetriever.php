<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event\EventRetriever;

use Magento\AdobeCommerceEventsClient\Api\Data\EventInterface;
use Magento\AdobeCommerceEventsClient\Event\EventRetrieverInterface;
use Magento\AdobeCommerceEventsClient\Model\ResourceModel\Event\CollectionFactory;

/**
 * Class for retrieving stored event data.
 */
class PriorityWaitingEventRetriever implements EventRetrieverInterface
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * @var CollectionToArrayConverter
     */
    private CollectionToArrayConverter $arrayConverter;

    /**
     * @param CollectionFactory $collectionFactory
     * @param CollectionToArrayConverter $arrayConverter
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        CollectionToArrayConverter $arrayConverter
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->arrayConverter = $arrayConverter;
    }

    /**
     * Returns a collection of events with waiting status and high priority.
     *
     * {@inheritDoc}
     */
    public function getEvents(): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('status', (string)EventInterface::WAITING_STATUS);
        $collection->addFieldToFilter('priority', EventInterface::PRIORITY_HIGH);

        return $this->arrayConverter->convert($collection);
    }
}
