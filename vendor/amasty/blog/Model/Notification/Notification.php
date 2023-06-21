<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Notification;

use Amasty\Blog\Helper\Settings;
use Amasty\Blog\Model\Comments as CommentsModel;
use Amasty\Blog\Model\ConfigProvider;
use Amasty\Blog\Model\Source\CommentStatus;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Mail\Template\TransportBuilder;

class Notification
{
    const NOTIFICATION_TYPE = 'amblog/comments/notify_about_replies';

    /**
     * @var Settings
     */
    private $settingsHelper;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var UrlInterface
     */
    private $backendUrl;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GetReceivers
     */
    private $getReceivers;

    /**
     * @var GetUnsubscribeLink
     */
    private $getUnsubscribeLink;

    public function __construct(
        Settings $settingsHelper,
        UrlInterface $backendUrl,
        TransportBuilder $transportBuilder,
        ConfigProvider $configProvider,
        GetReceivers $getReceivers,
        GetUnsubscribeLink $getUnsubscribeLink
    ) {
        $this->settingsHelper = $settingsHelper;
        $this->transportBuilder = $transportBuilder;
        $this->backendUrl = $backendUrl;
        $this->configProvider = $configProvider;
        $this->getReceivers = $getReceivers;
        $this->getUnsubscribeLink = $getUnsubscribeLink;
    }

    /**
     * @param int $storeId
     * @param \Amasty\Blog\Api\Data\PostInterface $post
     * @param CommentsModel $comment
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function commentNotificationForAdmin($storeId, $comment, $post)
    {
        if (!$this->settingsHelper->getCommentNotificationsEnabled()) {
            return;
        }

        $template = $this->settingsHelper->getNotificationEmailTemplate();
        $sender = $this->settingsHelper->getNotificationSender();
        $receivers = $this->settingsHelper->getNotificationRecievers();

        if ($template && $receivers) {
            $vars = [
                'post_title'    => $post->getTitle(),
                'post_link'    => $post->getUrl(),
                'comment_name' => $comment->getName(),
                'comment_email' => $comment->getEmail(),
                'comment_message' => $comment->getMessage(),
                'link' => $this->getCommentUrl($comment->getCommentId()),
                'need_approval' => $comment->getStatus() == CommentStatus::STATUS_PENDING
            ];

            $this->send($template, $storeId, $vars, $sender, $receivers);
        }
    }

    public function notifyCustomersReplies(CommentsModel $comment): void
    {
        if (!$this->configProvider->isNotifyAboutReplies()) {
            return;
        }

        $replyToId = (int) $comment->getReplyTo();
        $post = $comment->getPost();
        $template = $this->configProvider->notifyAboutRepliesTemplate();
        $sender = $this->configProvider->notifyAboutRepliesSender();
        $receivers = $this->getReceivers->execute($comment->getEmail(), $replyToId, (int) $post->getId());

        if ($template && $receivers && $sender) {
            $vars = [
                'post_link'    => sprintf('%s#am-blog-comment-%d', $post->getUrl(), $replyToId),
                'reply_name' => $comment->getName() ?: __('Guest'),
                'reply_date' => $comment->getCreatedAt(),
                'reply_message' => $comment->getMessage()
            ];

            foreach ($receivers as $receiver) {
                $vars['unsubscribe_link'] = $this->getUnsubscribeLink->execute($receiver);
                $this->send($template, (int) $comment->getStoreId(), $vars, $sender, $receiver);
            }
        }
    }

    private function send(string $template, int $storeId, array $vars, string $sender, $receiver): void
    {
        $transport = $this->transportBuilder->setTemplateIdentifier(
            $template
        )->setTemplateOptions(
            [
                'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ]
        )->setTemplateVars(
            $vars
        )->setFrom(
            $sender
        )->addTo(
            $receiver
        )->getTransport();

        $transport->sendMessage();
    }

    /**
     * @param int $commentId
     *
     * @return string
     */
    protected function getCommentUrl($commentId)
    {
        return $this->backendUrl->getUrl(
            'amasty_blog/comments/edit',
            [
                'id' => $commentId
            ]
        );
    }
}
