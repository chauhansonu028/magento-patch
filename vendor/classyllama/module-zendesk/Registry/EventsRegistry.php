<?php
/**
 * @category    ClassyLlama
 * @package     Zendesk\Zendesk
 * @copyright   Copyright (c) 2020 Classy Llama
 */

namespace Zendesk\Zendesk\Registry;

use Magento\Framework\Event\Observer;

/**
 * Events Registry for sharing data between observers
 */
class EventsRegistry
{
    /**
     * @var Observer
     */
    private $eventData;

    /**
     * Set the eventData to the event that is passed in.
     *
     * @param Observer $event
     */
    public function set($event)
    {
        $this->eventData = $event;
    }

    /**
     * Returns the event data if it exists, otherwise just return false
     *
     * @return bool|mixed
     */
    public function get()
    {
        return $this->eventData ?? false;
    }
}
