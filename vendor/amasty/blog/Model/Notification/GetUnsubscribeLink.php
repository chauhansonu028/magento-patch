<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Notification;

use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\DataObject;

class GetUnsubscribeLink
{
    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(
        EventManager $eventManager
    ) {
        $this->eventManager = $eventManager;
    }

    public function execute(string $email): string
    {
        $transportObject = new DataObject(
            [
                'type' => Notification::NOTIFICATION_TYPE,
                'email' => $email
            ]
        );
        $this->eventManager->dispatch(
            'amasty_get_unsubscribe_link',
            [
                'transport_object' => $transportObject
            ]
        );

        return $transportObject->getData('link') ?: '';
    }
}
