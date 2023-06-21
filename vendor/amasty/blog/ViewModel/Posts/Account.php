<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\ViewModel\Posts;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Api\Data\VoteInterface;
use Amasty\Blog\Helper\Date;
use Amasty\Blog\Helper\Settings;
use Amasty\Blog\Model\Posts;
use Amasty\Blog\Model\Repository\PostRepository;
use Amasty\Blog\Model\ResourceModel\Comments\Collection as CommentsCollection;
use Amasty\Blog\Model\ResourceModel\Comments\CollectionFactory as CommentsCollectionFactory;
use Amasty\Blog\Model\ResourceModel\Vote\Collection as VoteCollection;
use Amasty\Blog\Model\ResourceModel\Vote\CollectionFactory as VoteCollectionFactory;
use Amasty\Blog\Model\UrlResolver;
use Amasty\Blog\Model\Vote;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Pager;
use Psr\Log\LoggerInterface;

class Account implements ArgumentInterface
{
    public const PAGE_SIZE = 10;
    public const ALL_STORE_VIEWS = '0';

    public const COMMENTS_TOOLBAR_BLOCK_NAME = 'amblog_account_comments_pagination';
    public const COMMENTS_PAGE_VAR_NAME = 'cp';

    public const LIKED_TOOLBAR_BLOCK_NAME = 'amblog_account_liked_pagination';
    public const LIKED_PAGE_VAR_NAME = 'lp';

    /**
     * @var CommentsCollectionFactory
     */
    private $commentsCollectionFactory;

    /**
     * @var VoteCollectionFactory
     */
    private $voteCollectionFactory;

    /**
     * @var Date
     */
    private $helperDate;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    /**
     * @var array
     */
    private $posts = [];

    /**
     * @var PostRepository
     */
    private $postRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CommentsCollection|null
     */
    private $commentsCollection = null;

    /**
     * @var VoteCollection|null
     */
    private $likedCollection = null;

    /**
     * @var Pager|null
     */
    private $commentsToolbar = null;

    /**
     * @var Pager|null
     */
    private $likedToolbar = null;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var CustomerSessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Date $helperDate,
        RemoteAddress $remoteAddress,
        CommentsCollectionFactory $commentsCollectionFactory,
        VoteCollectionFactory $voteCollectionFactory,
        UrlResolver $urlResolver,
        PostRepository $postRepository,
        LoggerInterface $logger,
        LayoutInterface $layout,
        CustomerSessionFactory $customerSessionFactory,
        Settings $settings,
        StoreManagerInterface $storeManager
    ) {
        $this->commentsCollectionFactory = $commentsCollectionFactory;
        $this->helperDate = $helperDate;
        $this->voteCollectionFactory = $voteCollectionFactory;
        $this->remoteAddress = $remoteAddress;
        $this->urlResolver = $urlResolver;
        $this->postRepository = $postRepository;
        $this->logger = $logger;
        $this->layout = $layout;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->settings = $settings;
        $this->storeManager = $storeManager;
    }

    public function getCommentsCollection(): CommentsCollection
    {
        if (!$this->commentsCollection) {
            $this->commentsCollection = $this->commentsCollectionFactory
                ->create()
                ->addActiveFilter()
                ->addAllowedPostFilter()
                ->addCustomerIdFilter($this->getCustomerId())
                ->addStoreFilter([$this->storeManager->getStore()->getId()])
                ->sortByCreatedAt()
                ->setCurPage($this->getCommentsToolbarBlock()->getCurrentPage())
                ->setPageSize(self::PAGE_SIZE);
        }

        return $this->commentsCollection;
    }

    private function getCustomerId(): int
    {
        return (int) $this->customerSessionFactory->create()->getCustomerId();
    }

    public function getLikedCollection()
    {
        if (!$this->likedCollection) {
            $remoteAddress = $this->remoteAddress->getRemoteAddress();
            $this->likedCollection = $this->voteCollectionFactory
                ->create()
                ->addIpFilter($remoteAddress)
                ->addFieldToFilter(VoteInterface::TYPE, 1)
                ->addStoreFilter([self::ALL_STORE_VIEWS, $this->storeManager->getStore()->getId()])
                ->sortByPost()
                ->setCurPage($this->getLikedToolbarBlock()->getCurrentPage())
                ->setPageSize(self::PAGE_SIZE);
            $this->likedCollection->getSelect()->group('main_table.post_id');
        }

        return $this->likedCollection;
    }

    public function getComentsToolbar(): Pager
    {
        return $this->getCommentsToolbarBlock()
            ->setLimit(self::PAGE_SIZE)
            ->setCollection($this->getCommentsCollection());
    }

    public function getLikedToolbar(): Pager
    {
        return $this->getLikedToolbarBlock()
            ->setLimit(self::PAGE_SIZE)
            ->setCollection($this->getLikedCollection());
    }

    /**
     * @return Pager
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCommentsToolbarBlock(): Pager
    {
        if (!$this->commentsToolbar) {
            $toolbar = $this->layout->createBlock(Pager::class)
                ->setFragment(self::COMMENTS_PAGE_VAR_NAME)
                ->setAvailableLimit([])
                ->setPageVarName(self::COMMENTS_PAGE_VAR_NAME);
            $this->layout->setBlock(self::COMMENTS_TOOLBAR_BLOCK_NAME, $toolbar);
            $this->commentsToolbar = $toolbar;
        }

        return $this->commentsToolbar;
    }

    /**
     * @return Pager
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLikedToolbarBlock(): Pager
    {
        if (!$this->likedToolbar) {
            $toolbar = $this->layout->createBlock(Pager::class)
                ->setFragment(self::LIKED_PAGE_VAR_NAME)
                ->setAvailableLimit([])
                ->setPageVarName(self::LIKED_PAGE_VAR_NAME);
            $this->layout->setBlock(self::LIKED_TOOLBAR_BLOCK_NAME, $toolbar);
            $this->likedToolbar = $toolbar;
        }

        return $this->likedToolbar;
    }

    /**
     * @param $datetime
     * @param $isEditedAt
     * @return string
     */
    public function renderDate($datetime, bool$isEditedAt = false)
    {
        return $this->helperDate->renderDate($datetime, false, false, $isEditedAt);
    }

    public function getBlogUrl(): string
    {
        try {
            return $this->urlResolver->getBlogUrl();
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * @return PostInterface|Posts
     */
    public function getPost(Vote $vote): ?PostInterface
    {
        try {
            $postId = $vote->getPostId();
            if (!isset($this->posts[$postId])) {
                $this->posts[$postId] = $this->postRepository->getById((int)$postId);
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->logger->critical($e->getMessage());
        }

        return $this->posts[$postId] ?? null;
    }

    public function isLikeEnabled(): bool
    {
        return (bool) $this->settings->getHelpfulEnabled();
    }

    public function isCommentEnabled(): bool
    {
        return (bool) $this->settings->getUseComments();
    }
}
