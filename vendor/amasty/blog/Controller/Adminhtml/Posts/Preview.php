<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Adminhtml\Posts;

use Amasty\Base\Model\Serializer;
use Amasty\Blog\Model\Cache\Type\Blog;
use Amasty\Blog\Model\PostsFactory;
use Amasty\Blog\Model\Preview\Encryptor;
use Amasty\Blog\Model\Preview\PrepareForView;
use Amasty\Blog\Model\Repository\PostRepository;
use Amasty\Blog\Model\ResourceModel\Posts\RelatedProducts\GetPostRelatedProductsForPreview;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Url;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Preview extends \Magento\Backend\App\Action
{
    /**
     * @var PostsFactory
     */
    private $postsFactory;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var PostRepository
     */
    private $repository;

    /**
     * @var Url
     */
    private $urlHelper;

    /**
     * @var Blog
     */
    private $cache;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * @var StateInterface
     */
    private $cacheState;

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

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Context $context,
        PostsFactory $postsFactory,
        JsonFactory $resultJsonFactory,
        PostRepository $repository,
        Url $urlHelper,
        Blog $cache,
        Serializer $serializer,
        Random $mathRandom,
        StateInterface $cacheState,
        PrepareForView $prepareForView,
        Encryptor $encryptor,
        DateTime $dateTime,
        StoreManagerInterface $storeManager = null // TODO: move to not optional
    ) {
        parent::__construct($context);
        $this->postsFactory = $postsFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->repository = $repository;
        $this->urlHelper = $urlHelper;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->mathRandom = $mathRandom;
        $this->cacheState = $cacheState;
        $this->prepareForView = $prepareForView;
        $this->encryptor = $encryptor;
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManager ?? ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        $messages = [];
        $data = $this->getPostData();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $urlScopeParams = $this->getUrlScopeParams((int)$data['store_id']);

        if (!$this->cacheState->isEnabled(Blog::TYPE_IDENTIFIER) && isset($data['post_id'])) {
            $encryptedParams = $this->encryptor->encryptParams((int)$data['post_id'], $this->dateTime->gmtTimestamp());
            $url = $this->urlHelper->getUrl(
                'amblog/post/preview',
                array_merge($urlScopeParams, ['amblog_key_params' => $encryptedParams])
            );

            return $this->getRequest()->isAjax()
                ? $this->resultJsonFactory->create()->setData(['url' => $url])
                : $resultRedirect->setUrl($url);
        }

        if ($data) {
            try {
                $post = $this->postsFactory->create(['data' => $data]);
                $this->prepareForView->execute($post);
                $key = $this->savePostData($post->getData());
            } catch (\Exception $e) {
                $messages[] = __('An error occurred while execution');
                $messages[] = $e->getMessage();
            }
        } else {
            $messages[] = __('Empty Data for the post for preview');
        }

        if ($this->getRequest()->isAjax()) {
            if (!empty($messages)) {
                return $this->resultJsonFactory->create()->setData(
                    [
                        'messages' => $messages,
                        'error' => true
                    ]
                );
            }

            return $this->resultJsonFactory->create()->setData([
                'url' => $this->urlHelper->getUrl(
                    'amblog/post/preview',
                    array_merge($urlScopeParams, ['amblog_key' => $key])
                )
            ]);
        } else {
            if (!empty($messages)) {
                foreach ($messages as $message) {
                    $this->messageManager->addErrorMessage($message);
                }

                return $resultRedirect->setPath('*/*/');
            }

            $url = $this->urlHelper->getUrl(
                'amblog/post/preview',
                array_merge($urlScopeParams, ['amblog_key' => $key])
            );

            return $resultRedirect->setUrl($url);
        }
    }

    /**
     * @param array $data
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function savePostData($data)
    {
        $key = $this->mathRandom->getRandomString(16);
        $data = $this->serializer->serialize($data);
        $this->cache->save($data, $key, ['amblog_preview']);

        return $key;
    }

    /**
     * @return array
     */
    protected function getPostData()
    {
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $postId = $this->getRequest()->getParam('id', false);
            if ($postId) {
                try {
                    $post = $this->repository->getById($postId);
                    $post->setData(GetPostRelatedProductsForPreview::IS_PREVIEW_FROM_SAVED_FLAG, true);
                    $data = $post->getData();
                } catch (NoSuchEntityException $e) {
                    $data = [];
                }
            }
        }
        return $data;
    }

    private function getUrlScopeParams(int $storeId): array
    {
        try {
            $scope = $storeId === Store::DEFAULT_STORE_ID
                ? $this->storeManager->getDefaultStoreView()
                : $this->storeManager->getStore($storeId);
        } catch (NoSuchEntityException $e) {
            // If we can't find the store, use default
            $scope = Store::DEFAULT_STORE_ID;
        }

        return [
            '_scope' => $scope,
            '_scope_to_url' => true
        ];
    }
}
