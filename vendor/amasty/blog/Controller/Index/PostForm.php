<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Index;

use Amasty\Base\Model\Serializer;
use Amasty\Blog\Api\Data\CommentInterface;
use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Helper\Settings;
use Amasty\Blog\Model\Blog\Registry;
use Amasty\Blog\Model\Source\CommentStatus;
use Magento\Framework\App\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\ResultFactory;

class PostForm extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var \Amasty\Blog\Api\PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $sessionFactory;

    /**
     * @var Settings
     */
    private $settingsHelper;

    /**
     * @var \Amasty\Blog\Api\CommentRepositoryInterface
     */
    private $commentRepository;

    /**
     * @var Action\Context
     */
    private $context;

    /**
     * @var \Amasty\Blog\Helper\Url
     */
    private $urlHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $objectFactory;

    /**
     * @var \Amasty\Blog\Model\Notification\Notification
     */
    private $notificationModel;

    /**
     * @var \Amasty\Blog\Block\Comments\Form
     */
    private $form;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        Registry $registry,
        \Amasty\Blog\Helper\Settings $settingsHelper,
        \Amasty\Blog\Api\PostRepositoryInterface $postRepository,
        \Amasty\Blog\Api\CommentRepositoryInterface $commentRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Amasty\Blog\Helper\Url $urlHelper,
        \Magento\Framework\DataObjectFactory $objectFactory,
        \Amasty\Blog\Model\Notification\Notification $notificationModel,
        \Amasty\Blog\Block\Comments\Form $form,
        RedirectInterface $redirect,
        Serializer $serializer = null // TODO move to not optional
    ) {
        parent::__construct($context);
        $this->storeManagerInterface = $storeManagerInterface;
        $this->postRepository = $postRepository;
        $this->registry = $registry;
        $this->sessionFactory = $sessionFactory;
        $this->settingsHelper = $settingsHelper;
        $this->commentRepository = $commentRepository;
        $this->context = $context;
        $this->urlHelper = $urlHelper;
        $this->objectFactory = $objectFactory;
        $this->notificationModel = $notificationModel;
        $this->form = $form;
        $this->redirect = $redirect;
        $this->serializer = $serializer ?? ObjectManager::getInstance()->get(Serializer::class);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $result = [];

        $postData = $this->getRequest()->getPost()->toArray() ? : $this->getRequest()->getParams();
        $postData['store_id'] = (int)$this->storeManagerInterface->getStore()->getId();
        $postDataObject = $this->objectFactory->create(['data' => $postData]);

        if ($postId = (int)$this->getRequest()->getParam('post_id')) {
            try {
                $postInstance = $this->postRepository->getById($postId);
                $this->registry->unregister(Registry::CURRENT_POST);
                $this->registry->register(Registry::CURRENT_POST, $postInstance);

                $postDataObject->setPostId($postId);
                if ($this->getCustomerSession()->getCustomer()->getEntityId()
                    || $this->settingsHelper->getCommentsAllowGuests()
                ) {
                    $newComment = null;

                    $replyTo = (int)$postDataObject->getReplyTo();
                    if ($replyTo) {
                        try {
                            $newComment = $this->createComment($postDataObject->getData(), $postInstance);
                        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                            $this->context->getMessageManager()
                                ->addErrorMessage(__('The message for reply wasn`t found'));
                            $result['error'] = $e->getMessage();
                        }
                    } else {
                        $postDataObject->unsetData('reply_to');
                        $newComment = $this->createComment($postDataObject->getData(), $postInstance);
                    }

                    if ($newComment) {
                        $message = $this->_view->getLayout()
                            ->createBlock(\Amasty\Blog\Block\Comments\Message::class)
                            ->setTemplate('Amasty_Blog::comments/list/message.phtml');
                        if ($message) {
                            $this->form->setIsAjaxRendering(true);
                            $message->setMessage($newComment);
                            $message->setIsAjax(true);
                            $result['message'] = $message->toHtml();
                            $result['comment_id'] = $newComment->getCommentId();
                            $result['form'] = $this->form->toHtml();
                        }
                    } else {
                        $result['error'] = __('Can not create comment.');
                        $this->context->getMessageManager()->addErrorMessage(__('Can not create comment.'));
                    }
                } else {
                    $this->context->getMessageManager()->addErrorMessage(
                        __('Your session was expired. Please refresh this page and try again.')
                    );
                }
            } catch (\Exception $e) {
                $this->context->getMessageManager()->addErrorMessage(__('Post is not found.'));
                $result['error'] = $e->getMessage();
            }
        }

        if ($this->getRequest()->getParam('is_amp')) {
            $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $refererUrl = $this->redirect->getRefererUrl();

            if (strpos($refererUrl, 'amcomment') === false) {
                $refererUrl .= strpos($refererUrl, '?') !== false ? '&amcomment=1' : '?amcomment=1';
            }

            return $redirect->setPath($refererUrl);
        } else {
            $this->ajaxResponse($result);
        }
    }

    private function createComment(array $data, PostInterface $postInstance): CommentInterface
    {
        $storeId = (int) $this->storeManagerInterface->getStore()->getId();
        $comment = $this->commentRepository->getComment();

        $comment->addData($data);
        $comment->setStoreId($storeId);
        $this->resolveCustomerData($comment);
        if ($this->settingsHelper->getCommentsAutoapprove()) {
            $comment->setStatus(CommentStatus::STATUS_APPROVED);
            $comment->setSessionId(null);
        } else {
            $comment->setStatus(CommentStatus::STATUS_PENDING);
        }
        $comment->setMessage($this->prepareComment($data['message'] ?? ''));
        $comment = $this->commentRepository->save($comment);

        $this->_eventManager->dispatch(
            'custom_checkbox_confirm_log',
            ['customer' => $this->sessionFactory->create()->getCustomer()]
        );
        try {
            $this->notificationModel->commentNotificationForAdmin($storeId, $comment, $postInstance);
        } catch (\Exception $exception) {
            $this->context->getMessageManager()->addErrorMessage(
                __('Can not send email notification.')
            );
        }

        return $comment;
    }

    private function resolveCustomerData(CommentInterface $comment): void
    {
        $session = $this->getCustomerSession();
        $customer = $session->getCustomer();
        if ($session->isLoggedIn()) {
            $comment->setCustomerId((int)$session->getCustomerId());
        }
        if (!$comment->getName() && $customer->getEntityId()) {
            $comment->setName($customer->getName() ? : null);
        }
        if (!$comment->getEmail() && $customer->getEntityId()) {
            $comment->setEmail($customer->getEmail() ? : null);
        }
    }

    /**
     * @param $message
     * @return string
     */
    private function prepareComment($message)
    {
        $message = htmlspecialchars_decode($message);
        $message = strip_tags($message);
        $message = trim($message);

        return $message;
    }

    /**
     * @param array $result
     */
    private function ajaxResponse($result = [])
    {
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->setBody($this->serializer->serialize($result));
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    private function getCustomerSession()
    {
        return $this->sessionFactory->create();
    }
}
