<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Repository;

use Amasty\Blog\Api\CommentRepositoryInterface;
use Amasty\Blog\Api\Data\CommentInterface;
use Amasty\Blog\Helper\Settings;
use Amasty\Blog\Model\CommentsFactory;
use Amasty\Blog\Model\ResourceModel\Comments as CommentResource;
use Amasty\Blog\Model\ResourceModel\Comments\Collection;
use Amasty\Blog\Model\ResourceModel\Comments\CollectionFactory;
use Amasty\Blog\Model\Source\CommentStatus;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;

class CommentRepository implements CommentRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CommentsFactory
     */
    private $commentFactory;

    /**
     * @var CommentResource
     */
    private $commentResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $comments;

    /**
     * @var CollectionFactory
     */
    private $commentCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        CommentsFactory $commentFactory,
        CommentResource $commentResource,
        CollectionFactory $commentCollectionFactory,
        StoreManagerInterface $storeManagerInterface,
        Settings $settings,
        EventManager $eventManager
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->commentFactory = $commentFactory;
        $this->commentResource = $commentResource;
        $this->commentCollectionFactory = $commentCollectionFactory;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->settings = $settings;
        $this->eventManager = $eventManager;
    }

    /**
     * @param CommentInterface $comment
     *
     * @return CommentInterface
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function save(CommentInterface $comment)
    {
        try {
            if ($comment->getCommentId()) {
                $comment = $this->getById($comment->getCommentId())->addData($comment->getData());
            } else {
                $comment->setCommentId(null);
            }
            $this->isNeedNotifyCustomers($comment);

            $this->commentResource->save($comment);
            $this->afterSave($comment);
        } catch (\Exception $e) {
            if ($comment->getCommentId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save comment with ID %1. Error: %2',
                        [$comment->getCommentId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new comment. Error: %1', $e->getMessage()));
        }

        return $this->getById($comment->getId());
    }

    private function afterSave(CommentInterface $comment): CommentInterface
    {
        $isNeedNotifyCustomers = $this->isNeedNotifyCustomers($comment);
        $commentId = $comment->getId();
        $comment = $this->getById($commentId);
        $this->comments[$commentId] = $comment;

        if ($isNeedNotifyCustomers) {
            $this->eventManager->dispatch('amasty_comment_save_after', ['comment' => $comment]);
        }

        return $comment;
    }

    private function isNeedNotifyCustomers(CommentInterface $comment): bool
    {
        return $comment->getReplyTo()
            && ($comment->getStatus() == CommentStatus::STATUS_APPROVED)
            && $comment->getData('status') != $comment->getOrigData('status');
    }

    /**
     * @param int $commentId
     *
     * @return CommentInterface
     * @throws NoSuchEntityException
     */
    public function getById($commentId)
    {
        if (!isset($this->comments[$commentId])) {
            /** @var \Amasty\Blog\Model\Comments $comment */
            $comment = $this->commentFactory->create();
            $this->commentResource->load($comment, $commentId);
            if (!$comment->getCommentId()) {
                throw new NoSuchEntityException(__('Comment with specified ID "%1" not found.', $commentId));
            }
            $this->comments[$commentId] = $comment;
        }

        return $this->comments[$commentId];
    }

    /**
     * @param CommentInterface $comment
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(CommentInterface $comment)
    {
        try {
            $this->commentResource->delete($comment);
            unset($this->comments[$comment->getCommentId()]);
        } catch (\Exception $e) {
            if ($comment->getCommentId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove comment with ID %1. Error: %2',
                        [$comment->getCommentId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove comment. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @param int $commentId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($commentId)
    {
        $commentModel = $this->getById($commentId);
        $this->delete($commentModel);

        return true;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface|\Magento\Ui\Api\Data\BookmarkSearchResultsInterface
     * @throws NoSuchEntityException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\Blog\Model\ResourceModel\Comments\Collection $commentCollection */
        $commentCollection = $this->commentCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $commentCollection);
        }

        $searchResults->setTotalCount($commentCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $commentCollection);
        }

        $commentCollection->setCurPage($searchCriteria->getCurrentPage());
        $commentCollection->setPageSize($searchCriteria->getPageSize());

        $comments = [];
        /** @var CommentInterface $comment */
        foreach ($commentCollection->getItems() as $comment) {
            $comments[] = $this->getById($comment->getCommentId());
        }

        $searchResults->setItems($comments);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $commentCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $commentCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $commentCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $commentCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $commentCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $commentCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }

    /**
     * @return \Amasty\Blog\Api\Data\CommentInterface
     */
    public function getComment()
    {
        return $this->commentFactory->create();
    }

    /**
     * @param Collection
     * @throws NoSuchEntityException
     */
    private function addStoreFilter($collection)
    {
        if (!$this->storeManagerInterface->isSingleStoreMode()) {
            $collection->addStoreFilter($this->storeManagerInterface->getStore()->getId());
        }
    }

    /**
     * @param $activeFilter
     * @param $messageId
     * @return Collection
     * @throws NoSuchEntityException
     */
    public function getRepliesCollection($activeFilter, $messageId)
    {
        $comments = $this->commentCollectionFactory->create();

        $this->addStoreFilter($comments);
        $comments->addActiveFilter($activeFilter);
        $comments->setDateOrder(\Magento\Framework\DB\Select::SQL_ASC)->setReplyToFilter($messageId);

        return $comments;
    }

    /**
     * @param $postId
     * @return Collection
     * @throws NoSuchEntityException
     */
    public function getCommentsInPost($postId)
    {
        $comments = $this->commentCollectionFactory->create();

        $this->addStoreFilter($comments);
        $comments->addPostFilter($postId);

        return $comments;
    }

    /**
     * @return Collection
     * @throws NoSuchEntityException
     */
    public function getCollection()
    {
        $collection = $this->commentCollectionFactory->create();
        $this->addStoreFilter($collection);

        return $collection;
    }

    /**
     * @return Collection
     * @throws NoSuchEntityException
     */
    public function getRecentComments()
    {
        $collection = $this->commentCollectionFactory->create();
        $this->addStoreFilter($collection);
        $collection->addActiveFilter()->addAllowedPostFilter()->setDateOrder();

        return $collection;
    }
}
