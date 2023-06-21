<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event;

use Magento\AdobeCommerceEventsClient\Api\Data\EventInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Lock\LockManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class for sending event data in batches to the configured events service
 */
class EventBatchSender
{
    private const LOCK_NAME = 'event_batch_sender';
    private const LOCK_TIMEOUT = 60;

    private const CONFIG_PATH_MAX_RETRIES = 'adobe_io_events/eventing/max_retries';

    /**
     * @var LockManagerInterface
     */
    private LockManagerInterface $lockManager;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $config;

    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var EventBatchGenerator
     */
    private EventBatchGenerator $batchGenerator;

    /**
     * @var EventRetrieverInterface
     */
    private EventRetrieverInterface $eventRetriever;

    /**
     * @var EventStatusUpdater
     */
    private EventStatusUpdater $statusUpdater;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param LockManagerInterface $lockManager
     * @param ScopeConfigInterface $config
     * @param Client $client
     * @param EventBatchGenerator $batchGenerator
     * @param EventRetrieverInterface $eventRetriever
     * @param EventStatusUpdater $statusUpdater
     * @param LoggerInterface $logger
     */
    public function __construct(
        LockManagerInterface $lockManager,
        ScopeConfigInterface $config,
        Client $client,
        EventBatchGenerator $batchGenerator,
        EventRetrieverInterface $eventRetriever,
        EventStatusUpdater $statusUpdater,
        LoggerInterface $logger
    ) {
        $this->lockManager = $lockManager;
        $this->config = $config;
        $this->client = $client;
        $this->batchGenerator = $batchGenerator;
        $this->eventRetriever = $eventRetriever;
        $this->statusUpdater = $statusUpdater;
        $this->logger = $logger;
    }

    /**
     * Sends events data in batches.
     *
     * Reads stored event data waiting to be sent, sends the data in batches to the Events Service, and updates stored
     * events based on the success or failure of sending the data.
     * Added locking mechanism to avoid the situation when two processes trying to process the same events.
     *
     * @return void
     * @throws LocalizedException
     */
    public function sendEventDataBatches(): void
    {
        $didLock = false;
        try {
            if (!$this->lockManager->lock(self::LOCK_NAME, self::LOCK_TIMEOUT)) {
                return;
            }

            $didLock = true;
            $waitingEvents = $this->eventRetriever->getEvents();
            $this->statusUpdater->updateStatus(
                array_keys($waitingEvents),
                EventInterface::SENDING_STATUS
            );
        } finally {
            if ($didLock) {
                $this->lockManager->unlock(self::LOCK_NAME);
            }
        }

        while (count($waitingEvents) != 0) {
            $eventBatch = $this->batchGenerator->generateBatch($waitingEvents);
            $eventIds = array_keys($eventBatch);
            $eventData = array_values($eventBatch);

            try {
                $response = $this->client->sendEventDataBatch($eventData);

                if ($response->getStatusCode() == 200) {
                    $this->logger->info(sprintf(
                        'Event data batch of %s events was successfully published.',
                        count($eventData)
                    ));
                    $this->statusUpdater->updateStatus($eventIds, EventInterface::SUCCESS_STATUS);
                    $waitingEvents = $this->unsetEvents($waitingEvents, $eventIds);
                } else {
                    $errorMessage = sprintf(
                        'Error code: %d; reason: %s %s',
                        $response->getStatusCode(),
                        $response->getReasonPhrase(),
                        $response->getBody()->getContents()
                    );
                    $this->logger->error(sprintf(
                        'Publishing of batch of %s events failed. %s',
                        count($eventBatch),
                        $errorMessage
                    ));
                    $failedStatusEvents = $this->setFailure($eventIds, 'Event publishing failed: ' . $errorMessage);
                    $waitingEvents = $this->unsetEvents($waitingEvents, $failedStatusEvents);
                }
            } catch (Throwable $exception) {
                $this->logger->error(sprintf(
                    'Publishing of batch of %s events failed: %s',
                    count($eventBatch),
                    $exception->getMessage()
                ));
                $failedStatusEvents = $this->setFailure(
                    $eventIds,
                    'Event publishing failed: ' . $exception->getMessage()
                );
                $waitingEvents = $this->unsetEvents($waitingEvents, $failedStatusEvents);
            }
        }
    }

    /**
     * Sets failure status from provided event ids.
     *
     * @param array $eventIds
     * @param string $info
     * @return array
     */
    private function setFailure(array $eventIds, string $info = ''): array
    {
        $maxRetries = (int)$this->config->getValue(self::CONFIG_PATH_MAX_RETRIES);
        return $this->statusUpdater->updateFailure($eventIds, $maxRetries, $info);
    }

    /**
     * Unsets events from the array.
     *
     * @param array $events
     * @param array $eventIds
     * @return array
     */
    private function unsetEvents(array $events, array $eventIds): array
    {
        foreach ($eventIds as $eventId) {
            unset($events[$eventId]);
        }
        return $events;
    }
}
