<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Notification;

use Amasty\Blog\Model\ResourceModel\Comments;
use Amasty\Blog\Model\Source\CommentStatus;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManager;

class GetReceivers
{
    /**
     * @var Comments
     */
    private $comments;

    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(
        Comments $comments,
        EventManager $eventManager
    ) {
        $this->comments = $comments;
        $this->eventManager = $eventManager;
    }

    public function execute(?string $customerEmail, int $replyTo, int $postId): array
    {
        $receivers = $this->comments->getReplyIdsByCommentId($replyTo, CommentStatus::STATUS_APPROVED);
        $parentIds = $this->getReceiverIds(
            $this->comments->getCommentsByPostId($postId, CommentStatus::STATUS_APPROVED),
            $replyTo
        );
        $receivers = array_merge($receivers, $parentIds);

        $emails = array_unique($this->comments->getEmailsByCommentId($receivers));
        if (($key = array_search($customerEmail, $emails)) !== false) {
            unset($emails[$key]);
        }

        return $this->removeUnsubscribedCustomers($emails);
    }

    private function removeUnsubscribedCustomers(array $emails): array
    {
        $transportObject = new DataObject(
            [
                'type' => Notification::NOTIFICATION_TYPE,
                'emails' => $emails
            ]
        );
        $this->eventManager->dispatch(
            'amasty_update_unsubscribed_emails',
            [
                'transport_object' => $transportObject
            ]
        );

        return $transportObject->getData('emails');
    }

    private function getReceiverIds(array $commentIds, int $commentId): array
    {
        $replyTo = (int) ($commentIds[$commentId] ?? null);
        if ($replyTo) {
            $result = $this->getReceiverIds($commentIds, $replyTo);
        }
        $result[] = $commentId;

        return $result;
    }
}
