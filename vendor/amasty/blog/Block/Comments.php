<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block;

use Amasty\Blog\Block\Comments\Message;
use Amasty\Blog\Helper\Data;
use Amasty\Blog\Helper\Date;
use Amasty\Blog\Helper\Settings;
use Amasty\Blog\Model\Blog\Registry;
use Amasty\Blog\Model\Comments as CommentModel;
use Amasty\Blog\Model\ConfigProvider;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;

class Comments extends Template implements IdentityInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Settings
     */
    private $settingsHelper;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var \Amasty\Blog\Helper\Date
     */
    private $dateHelper;

    /**
     * @var \Amasty\Blog\Api\CommentRepositoryInterface
     */
    private $commentRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\Blog\Helper\Settings $settingsHelper,
        \Amasty\Blog\Helper\Data $dataHelper,
        \Amasty\Blog\Helper\Date $dateHelper,
        \Magento\Customer\Model\Session $session,
        Registry $registry,
        \Amasty\Blog\Api\CommentRepositoryInterface $commentRepository,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->settingsHelper = $settingsHelper;
        $this->dataHelper = $dataHelper;
        $this->dateHelper = $dateHelper;
        $this->commentRepository = $commentRepository;
        $this->session = $session;
        $this->configProvider = $configProvider;
    }

    /**
     * @return \Amasty\Blog\Model\Posts|bool|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPost()
    {
        $result = false;
        $parent = $this->getParentBlock();

        if ($parent) {
            if ($parent instanceof \Amasty\Blog\Block\Content\Post) {
                $result = $parent->getPost();
            }
        }

        if (!$result) {
            $result = $this->getData('post') ?: $this->registry->registry(Registry::CURRENT_POST);
        }

        return $result;
    }

    /**
     * @return \Amasty\Blog\Model\ResourceModel\Comments\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCollection()
    {
        $comments = $this->commentRepository->getCommentsInPost($this->getPostId());

        $comments->addActiveFilter(
            $this->settingsHelper->getCommentsAutoapprove()
                ? null
                : $this->getSession()->getSessionId()
        );

        $comments->setDateOrder(\Magento\Framework\DB\Select::SQL_ASC)->setNotReplies();

        return $comments;
    }

    /**
     * @param CommentModel $message
     *
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMessageHtml(CommentModel $message)
    {
        $result = false;
        $messageBlock = $this->getLayout()->getBlock(Message::AMBLOG_COMMENTS_MESSAGE);
        if (!$messageBlock) {
            $messageBlock = $this->getLayout()
                ->createBlock(\Amasty\Blog\Block\Comments\Message::class, Message::AMBLOG_COMMENTS_MESSAGE)
                ->setTemplate("Amasty_Blog::comments/list/message.phtml");
        }
        if ($messageBlock) {
            $messageBlock->setMessage($message);
            $result = $messageBlock->toHtml();
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getFormUrl()
    {
        return $this->getUrl(
            'amblog/index/form',
            [
                'reply_to' => '{{reply_to}}',
                'post_id' => '{{post_id}}',
                'session_id' => '{{session_id}}',
            ]
        );
    }

    /**
     * @return string
     */
    public function getUpdateUrl()
    {
        return $this->getUrl('amblog/index/updateComments');
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPostId()
    {
        return (int)$this->getPost()->getId();
    }

    /**
     * @return string
     */
    public function getPostUrl()
    {
        return $this->getUrl(
            'amblog/index/postForm',
            [
                'reply_to' => '{{reply_to}}',
                'post_id' => '{{post_id}}',
            ]
        );
    }

    /**
     * @return string
     */
    public function getColorClass()
    {
        return $this->getSettingsHelper()->getIconColorClass();
    }

    /**
     * @return bool
     */
    public function commentsEnabled()
    {
        return $this->settingsHelper->getUseComments();
    }

    /**
     * @return \Amasty\Blog\Helper\Date
     */
    public function getDateHelper()
    {
        return $this->dateHelper;
    }

    /**
     * @return Data
     */
    public function getDataHelper()
    {
        return $this->dataHelper;
    }

    /**
     * @return Settings
     */
    public function getSettingsHelper()
    {
        return $this->settingsHelper;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @return \Amasty\Blog\Api\CommentRepositoryInterface
     */
    public function getRepository()
    {
        return $this->commentRepository;
    }

    /**
     * @return ConfigProvider
     */
    public function getConfigProvider(): ConfigProvider
    {
        return $this->configProvider;
    }

    /**
     * @return string[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getIdentities(): array
    {
        $comments = $this->getCollection()->getItems();

        return array_reduce($comments, function (array $carry, CommentModel $comment): array {
            return array_merge($carry, $comment->getIdentities());
        }, []);
    }

    public function isHumanized(): bool
    {
        return $this->configProvider->getDateFormat() === Date::DATE_TIME_PASSED;
    }
}
