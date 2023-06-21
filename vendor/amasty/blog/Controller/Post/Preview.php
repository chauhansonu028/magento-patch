<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Post;

use Amasty\Base\Model\Serializer;
use Amasty\Blog\Helper\Url;
use Amasty\Blog\Model\Blog\MetaDataResolver\Post as MetadataResolver;
use Amasty\Blog\Model\Blog\Registry;
use Amasty\Blog\Model\Cache\Type\Blog;
use Amasty\Blog\Model\PostsFactory;
use Amasty\Blog\Model\Preview\Encryptor;
use Amasty\Blog\Model\Preview\PrepareForView;
use Amasty\Blog\Model\Repository\PostRepository;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\App\Response\Redirect;

class Preview extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PostsFactory
     */
    private $postsFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Url
     */
    private $urlHelper;

    /**
     * @var Redirect
     */
    private $redirect;

    /**
     * @var Blog
     */
    private $cache;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var MetadataResolver
     */
    private $metadataResolver;

    /**
     * @var PostRepository
     */
    private $postRepository;

    /**
     * @var PrepareForView
     */
    private $prepareForView;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        Context $context,
        Registry $registry,
        PostsFactory $postsFactory,
        Redirect $redirect,
        Url $urlHelper,
        Blog $cache,
        Serializer $serializer,
        MetadataResolver $metadataResolver,
        PageFactory $resultPageFactory,
        PostRepository $postRepository,
        PrepareForView $prepareForView,
        Encryptor $encryptor,
        DateTime $dateTime
    ) {
        parent::__construct($context);
        $this->postsFactory = $postsFactory;
        $this->registry = $registry;
        $this->urlHelper = $urlHelper;
        $this->redirect = $redirect;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->resultPageFactory = $resultPageFactory;
        $this->metadataResolver = $metadataResolver;
        $this->postRepository = $postRepository;
        $this->prepareForView = $prepareForView;
        $this->encryptor = $encryptor;
        $this->dateTime = $dateTime;
    }

    /**
     * @return ResponseInterface|ResultInterface|\Magento\Framework\View\Result\Page
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $data = $this->getSavedData();

        if ($data) {
            if (strpos($this->getRequest()->getPathInfo(), '/amp/') !== false) {
                $this->urlHelper->addAmpHeaders($this->getResponse());
            }

            $page = $this->resultPageFactory->create();
            $post = $this->postsFactory->create();
            $post->addData($data);
            $post->setIsPreviewPost(true);
            $post->setCommentsEnabled(false);
            $this->registry->unregister(Registry::CURRENT_POST);
            $this->registry->register(Registry::CURRENT_POST, $post);
            $this->metadataResolver->resolve($page, $post);

            return $page;
        } else {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            return $resultRedirect->setPath('noroute');
        }
    }

    /**
     * @return array
     */
    protected function getSavedData()
    {
        $data = [];
        $blogKey = $this->getRequest()->getParam('amblog_key');
        $params = $this->getRequest()->getParam('amblog_key_params');

        if ($blogKey) {
            $data = $this->cache->load($blogKey);

            if ($data) {
                $this->cache->remove($blogKey);
                $data = $this->serializer->unserialize($data);
            }
        } elseif ($params) {
            $params = $this->encryptor->decryptParams($params);

            if ($this->validateParams($params)) {
                $post = $this->postRepository->getById($params[Encryptor::POST_ID]);
                $this->prepareForView->execute($post);
                $data = $post->getData();
            }
        }

        return $data ?: [];
    }

    private function validateParams(array $params): bool
    {
        return isset($params[Encryptor::POST_ID])
            && isset($params[Encryptor::DATE_KEY])
            && $params[Encryptor::DATE_KEY] + 300 > $this->dateTime->gmtTimestamp();
    }
}
