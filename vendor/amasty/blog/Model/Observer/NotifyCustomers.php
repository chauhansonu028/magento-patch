<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Observer;

use Amasty\Blog\Model\Notification\Notification;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class NotifyCustomers implements ObserverInterface
{
    /**
     * @var Notification
     */
    private $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function execute(Observer $observer): void
    {
        $this->notification->notifyCustomersReplies($observer->getData('comment'));
    }
}
