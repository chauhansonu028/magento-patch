<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Comments;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Model\Blog\Registry;

class Form extends \Amasty\Blog\Block\Comments
{
    const KEY_CUSTOMER_NAME = 'amblog-customer-name';

    const KEY_CUSTOMER_EMAIL = 'amblog-customer-email';

    /**
     * @var string
     */
    protected $_template = 'Amasty_Blog::comments/form.phtml';

    /**
     * @var string|null
     */
    private $post;

    /**
     * @var string|null
     */
    private $replyTo;

    /**
     * @var bool
     */
    private $isAjaxRendering = false;

    /**
     * @return string
     */
    public function _toHtml()
    {
        if ($this->getPost()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @param $value
     * @return $this
     */
    public function setPost($value)
    {
        $this->post = $value;

        return $this;
    }

    /**
     * @param $value
     */
    public function setReplyTo($value)
    {
        $this->replyTo = $value;
    }

    /**
     * @return int
     */
    public function getReplyTo()
    {
        return $this->replyTo ? $this->replyTo->getId() : 0;
    }

    /**
     * @return PostInterface
     */
    public function getPost()
    {
        if (!$this->post) {
            $this->post = $this->getRegistry()->registry(Registry::CURRENT_POST);
        }

        return $this->post;
    }

    public function getPostId(): int
    {
        return (int)$this->getPost()->getId();
    }

    public function isReply(): bool
    {
        return (bool)$this->getReplyTo();
    }

    public function canPost(): bool
    {
        return (bool)$this->getPost()->getCommentsEnabled();
    }

    public function canUserPost(): bool
    {
        return $this->getSettingsHelper()->getCommentsAllowGuests() || $this->isLoggedIn();
    }

    public function getRegisterUrl(): string
    {
        return $this->getUrl('customer/account/create');
    }

    public function getLoginUrl(): string
    {
        $params = ['post_id' => $this->getPostId()];
        if ($this->isReply()) {
            $params['reply_to'] = $this->getReplyTo();
        }

        return $this->getUrl('customer/account/login', $params);
    }

    public function isLoggedIn(): bool
    {
        return (bool)$this->getSession()->isLoggedIn();
    }

    public function getCustomerId(): ?int
    {
        return (int)$this->getSession()->getCustomerId() ?: null;
    }

    public function getCustomerName(): ?string
    {
        return $this->isLoggedIn()
            ? $this->getSession()->getCustomer()->getName()
            : $this->getSession()->getData(self::KEY_CUSTOMER_NAME);
    }

    public function getCustomerEmail(): ?string
    {
        return $this->isLoggedIn()
            ? $this->getSession()->getCustomer()->getEmail()
            : $this->getSession()->getData(self::KEY_CUSTOMER_EMAIL);
    }

    public function getSessionId(): ?string
    {
        return $this->getData('session_id');
    }

    /**
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getReplyToCustomerName(): ?string
    {
        $comment = $this->getRepository()->getById($this->getReplyTo());

        return $comment->getId() ? $comment->getName() : null;
    }

    public function processGdpr(): void
    {
        $result = new \Magento\Framework\DataObject();
        $this->_eventManager->dispatch(
            'amasty_gdpr_get_checkbox',
            [
                'scope' => \Amasty\Blog\Model\Config\GdprBlog::GDPR_BLOG_COMMENT_FORM,
                'result' => $result
            ]
        );
        $this->setData('gdpr', $result);
    }

    public function getGdprCheckboxHtml(): string
    {
        if (!$this->hasData('gdpr')) {
            $this->processGdpr();
        }

        return $this->getData('gdpr')->getData('html') ?: '';
    }

    public function isGdprEnabled(): bool
    {
        return (bool)$this->getConfigProvider()->isShowGdpr();
    }

    public function getGdprText(): string
    {
        return $this->getConfigProvider()->getGdprText();
    }

    public function isAskEmail(): bool
    {
        return $this->getConfigProvider()->isAskEmail();
    }

    public function isAskName(): bool
    {
        return $this->getConfigProvider()->isAskName();
    }

    public function getAmpFormUrl(): string
    {
        $url = $this->getUrl(
            'amblog/index/postForm',
            [
                'post_id' => $this->getPostId(),
                'session_id' => $this->getSessionId(),
                'reply_to' => $this->getReplyTo()
            ]
        );

        return str_replace(['https:', 'http:'], '', $url);
    }

    public function isCommentAdded(): bool
    {
        return (bool)$this->getRequest()->getParam('amcomment');
    }

    public function isAjaxRendering(): bool
    {
        return $this->isAjaxRendering;
    }

    public function setIsAjaxRendering(bool $isAjaxRendering): void
    {
        $this->isAjaxRendering = $isAjaxRendering;
    }
}
