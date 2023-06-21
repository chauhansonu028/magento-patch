<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Repository;

use Amasty\Blog\Api\Data\TagInterface;
use Amasty\Blog\Api\TagRepositoryInterface;
use Amasty\Blog\Model\ResourceModel\Tag as TagResource;
use Amasty\Blog\Model\ResourceModel\Tag\Collection;
use Amasty\Blog\Model\ResourceModel\Tag\CollectionFactory;
use Amasty\Blog\Model\Source\PostStatus;
use Amasty\Blog\Model\TagFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class TagRepository implements TagRepositoryInterface
{
    /**
     * @var TagFactory
     */
    private $tagFactory;

    /**
     * @var TagResource
     */
    private $tagResource;

    /**
     * @var array
     */
    private $tags;

    /**
     * @var CollectionFactory
     */
    private $tagCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        TagFactory $tagFactory,
        TagResource $tagResource,
        CollectionFactory $tagCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->tagFactory = $tagFactory;
        $this->tagResource = $tagResource;
        $this->tagCollectionFactory = $tagCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @throws CouldNotSaveException
     */
    public function save(TagInterface $tag): TagInterface
    {
        try {
            if ($tag->getTagId()) {
                $tag = $this->getById($tag->getTagId())->addData($tag->getData());
            } else {
                $tag->setTagId(null);
            }
            $this->tagResource->save($tag);
            unset($this->tags[$tag->getTagId()]);
        } catch (\Exception $e) {
            if ($tag->getTagId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save tag with ID %1. Error: %2',
                        [$tag->getTagId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new tag. Error: %1', $e->getMessage()));
        }

        return $tag;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getById(int $tagId): TagInterface
    {
        if (!isset($this->tags[$tagId])) {
            /** @var \Amasty\Blog\Model\Tag $tag */
            $tag = $this->tagFactory->create();
            $this->tagResource->load($tag, $tagId);
            if (!$tag->getTagId()) {
                throw new NoSuchEntityException(__('Tag with specified ID "%1" not found.', $tagId));
            }
            $this->tags[$tagId] = $tag;
        }

        return $this->tags[$tagId];
    }

    public function getByUrlKey(?string $urlKey): TagInterface
    {
        return $this->getByUrlKeyAndStoreId($urlKey);
    }

    public function getByUrlKeyAndStoreId(?string $urlKey, ?int $storeId = Store::DEFAULT_STORE_ID): TagInterface
    {
        $collection = $this->tagCollectionFactory->create();
        $collection->addStoreWithDefault((int)$storeId);
        $collection->addStoreFieldToFilter(TagInterface::URL_KEY, $urlKey);
        $collection->setLimit(1);
        /** @var TagInterface $tagByUrlKey **/
        $tagByUrlKey = $collection->getFirstItem();

        return $tagByUrlKey;
    }

    /**
     * @throws CouldNotDeleteException
     */
    public function delete(TagInterface $tag): bool
    {
        try {
            $this->tagResource->delete($tag);
            unset($this->tags[$tag->getTagId()]);
        } catch (\Exception $e) {
            if ($tag->getTagId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove tag with ID %1. Error: %2',
                        [$tag->getTagId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove tag. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $tagId): bool
    {
        $tagModel = $this->getById($tagId);
        $this->delete($tagModel);

        return true;
    }

    public function getList(array $tags): Collection
    {
        return $this->tagCollectionFactory->create()
            ->addDefaultStore()
            ->addFieldToFilter(TagInterface::NAME, ['in' => $tags]);
    }

    public function getTagModel(): TagInterface
    {
        return $this->tagFactory->create();
    }

    public function getTagCollection(): Collection
    {
        return $this->tagCollectionFactory->create();
    }

    public function getTagsByPost(int $postId, ?int $storeId): Collection
    {
        $tags = $this->tagCollectionFactory->create();
        $tags->addStoreWithDefault((int)$storeId);
        $tags->addPostFilter($postId);

        return $tags;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getTagsByIds(array $tagsIds = []): Collection
    {
        $tags = $this->tagCollectionFactory->create();
        $storeId = $this->storeManager->getStore()->getId();
        $tags->addStoreWithDefault((int)$storeId)->addIdFilter($tagsIds);

        return $tags;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getActiveTags(?int $storeId = null): Collection
    {
        $tags = $this->tagCollectionFactory->create();
        $storeId = $storeId === null ? $this->storeManager->getStore()->getId() : $storeId;
        $tags->addStoreWithDefault((int)$storeId)->addWeightData($storeId);
        $tags->setPostStatusFilter(PostStatus::STATUS_ENABLED);

        return $tags;
    }

    public function getAllTags(): array
    {
        return $this->tagCollectionFactory->create()->addDefaultStore()->getItems();
    }

    public function getByIdAndStore(?int $tagId, ?int $storeId = 0, bool $isAddDefaultStore = true): TagInterface
    {
        $collection = $this->tagCollectionFactory->create();
        if ($isAddDefaultStore) {
            $collection->addStoreWithDefault((int)$storeId);
        } else {
            $collection->addStore($storeId);
        }

        $collection->addFieldToFilter(TagInterface::TAG_ID, $tagId);
        $collection->setLimit(1);

        return $collection->getFirstItem();
    }
}
