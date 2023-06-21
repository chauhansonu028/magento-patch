<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Api;

use Amasty\Blog\Api\Data\TagInterface;
use Amasty\Blog\Model\ResourceModel\Tag\Collection;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;

/**
 * @api
 */
interface TagRepositoryInterface
{
    public function save(TagInterface $tag): TagInterface;

    /**
     * @throws NoSuchEntityException
     */
    public function getById(int $tagId): TagInterface;

    /**
     * @deprecared since version 2.7.0 Now url key can be configured by store view
     * @see \Amasty\Blog\Api\TagRepositoryInterface::getByUrlKeyAndStoreId
     */
    public function getByUrlKey(?string $urlKey): TagInterface;

    public function getByUrlKeyAndStoreId(?string $urlKey, ?int $storeId = Store::DEFAULT_STORE_ID): TagInterface;

    /**
     * @throws CouldNotDeleteException
     */
    public function delete(TagInterface $tag): bool;

    /**
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $tagId): bool;

    public function getList(array $tags): Collection;

    public function getTagModel(): TagInterface;

    public function getTagCollection(): Collection;

    public function getTagsByPost(int $postId, ?int $storeId): Collection;

    public function getTagsByIds(array $tagsIds = []): Collection;

    public function getActiveTags(?int $storeId = null): Collection;

    public function getAllTags(): array;

    public function getByIdAndStore(?int $tagId, ?int $storeId = 0, bool $isAddDefaultStore = true): TagInterface;
}
