<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Repository;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Api\PostRepositoryInterface;
use Amasty\Blog\Helper\Settings;
use Amasty\Blog\Model\PostsFactory;
use Amasty\Blog\Model\ResourceModel\Posts as PostsResource;
use Amasty\Blog\Model\ResourceModel\Posts\CollectionFactory;
use Amasty\Blog\Model\Source\PostStatus;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PostRepository implements PostRepositoryInterface
{
    /**
     * @var PostsFactory
     */
    private $postFactory;

    /**
     * @var PostsResource
     */
    private $postResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $posts;

    /**
     * @var CollectionFactory
     */
    private $postCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        PostsFactory $postFactory,
        PostsResource $postResource,
        CollectionFactory $postCollectionFactory,
        StoreManagerInterface $storeManagerInterface,
        Settings $settings,
        ResourceConnection $resourceConnection
    ) {
        $this->postFactory = $postFactory;
        $this->postResource = $postResource;
        $this->postCollectionFactory = $postCollectionFactory;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->settings = $settings;
        $this->resourceConnection = $resourceConnection;
    }

    public function save(PostInterface $post): PostInterface
    {
        try {
            if ($post->getPostId()) {
                $post = $this->getById($post->getPostId())->addData($post->getData());
            } else {
                $post->setPostId(null);
            }
            $this->postResource->save($post);
            unset($this->posts[$post->getPostId()]);
        } catch (\Exception $e) {
            if ($post->getPostId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save posts with ID %1. Error: %2',
                        [$post->getPostId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new posts. Error: %1', $e->getMessage()));
        }

        return $post;
    }

    public function getPost(): PostInterface
    {
        return $this->postFactory->create();
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getById(int $postId): PostInterface
    {
        if (!isset($this->posts[$postId])) {
            /** @var \Amasty\Blog\Model\Posts $posts */
            $posts = $this->postFactory->create();
            $this->postResource->load($posts, $postId);
            if (!$posts->getPostId()) {
                throw new NoSuchEntityException(__('Posts with specified ID "%1" not found.', $postId));
            }
            $this->posts[$postId] = $posts;
        }

        return $this->posts[$postId];
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getByUrlKey(?string $urlKey): PostInterface
    {
        return $this->getByUrlKeyAndStoreId($urlKey);
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getByUrlKeyAndStoreId(?string $urlKey, ?int $storeId = null): PostInterface
    {
        $storeId = $storeId ?? (int) $this->storeManagerInterface->getStore()->getId();
        $collection = $this->postCollectionFactory->create();
        $collection->addStoreWithDefault($storeId);
        $collection->addStoreFieldToFilter(PostInterface::URL_KEY, $urlKey);
        $collection->addFilterByStatus([PostStatus::STATUS_ENABLED, PostStatus::STATUS_HIDDEN]);
        $collection->setLimit(1);
        /** @var PostInterface $postByUrlKey **/
        $postByUrlKey = $collection->getFirstItem();

        return $postByUrlKey;
    }

    public function getByUrlKeyWithAllStatuses(string $urlKey): PostInterface
    {
        $collection = $this->postCollectionFactory->create();
        $collection->addFieldToFilter('url_key', $urlKey)->setUrlKeyIsNotNull();
        $collection->setLimit(1);

        return $collection->getFirstItem();
    }

    /**
     * @throws CouldNotDeleteException
     */
    public function delete(PostInterface $post): bool
    {
        try {
            $this->postResource->delete($post);
            unset($this->posts[$post->getPostId()]);
        } catch (\Exception $e) {
            if ($post->getPostId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove posts with ID %1. Error: %2',
                        [$post->getPostId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove posts. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $postId): bool
    {
        $postsModel = $this->getById($postId);
        $this->delete($postsModel);

        return true;
    }

    public function getTaggedPosts(int $tagId): PostsResource\Collection
    {
        return $this->postCollectionFactory->create()->addTagFilter($tagId);
    }

    public function getPostCollection(): PostsResource\Collection
    {
        return $this->postCollectionFactory->create();
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getPostsByPage(int $page): PostsResource\Collection
    {
        return $this->getActivePosts()->setPageSize($this->settings->getPostsLimit())->setCurPage($page);
    }

    /**
     * @return PostsResource\Collection
     * @throws NoSuchEntityException
     */
    public function getRecentPosts(): PostsResource\Collection
    {
        $collection = $this->getActivePosts();
        $collection->setUrlKeyIsNotNull();
        $collection->setDateOrder();

        return $collection;
    }

    /**
     * @throws NoSuchEntityException
     */
    private function addStoreFilter(PostsResource\Collection $collection, ?int $storeId = null)
    {
        if (!$this->storeManagerInterface->isSingleStoreMode()) {
            $collection->addStoreWithDefault($storeId ?: (int)$this->storeManagerInterface->getStore()->getId());
        }
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getActivePosts(?int $storeId = null): PostsResource\Collection
    {
        if ($storeId === null) {
            $storeId = $this->storeManagerInterface->getStore()->getId();
        }

        /** @var PostsResource\Collection $posts */
        $posts = $this->postCollectionFactory->create();
        $posts->addStoreWithDefault((int)$storeId);
        $posts->addFilterByStatus([PostStatus::STATUS_ENABLED]);

        return $posts;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getFeaturedPosts(?int $storeId = null): PostsResource\Collection
    {
        $collection = $this->getActivePosts($storeId);
        $collection->addFieldToFilter(PostInterface::IS_FEATURED, 1)
            ->addFieldToFilter('main_table.list_thumbnail', ['neq' => 'NULL']);

        return $collection;
    }

    public function getByIdAndStore(?int $postId, int $storeId = 0, bool $isAddDefaultStore = true): DataObject
    {
        $collection = $this->postCollectionFactory->create();
        if ($isAddDefaultStore) {
            $collection->addStoreWithDefault((int)$storeId);
        } else {
            $collection->addStore($storeId);
        }

        $collection->addFieldToFilter(PostInterface::POST_ID, $postId);
        $collection->setLimit(1);

        return $collection->getFirstItem();
    }

    /**
     * @throws CouldNotSaveException
     */
    public function changeStatus(PostInterface $post, int $status): void
    {
        $post->setStatus($status);
        $post->setData('saveStoreData', false);
        $this->save($post);
        $connection = $this->resourceConnection->getConnection();
        $storeUpdate = [PostInterface::STATUS => $status];
        $storeTableName = $this->postResource->getTable(PostsResource::STORE_TABLE_NAME);
        $connection->update($storeTableName, $storeUpdate, [
            PostInterface::POST_ID . ' = ?' => $post->getPostId()
        ]);
    }
}
