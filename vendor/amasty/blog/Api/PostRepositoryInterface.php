<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Api;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Model\ResourceModel\Posts\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Amasty\Blog\Model\ResourceModel\Posts as PostsResource;
use Magento\Store\Model\Store;

/**
 * @api
 */
interface PostRepositoryInterface
{
    public function save(PostInterface $post): PostInterface;

    public function getById(int $postId): PostInterface;

    public function getByUrlKey(?string $urlKey): PostInterface;

    public function getByUrlKeyAndStoreId(?string $urlKey, int $storeId = Store::DEFAULT_STORE_ID): PostInterface;

    public function getByUrlKeyWithAllStatuses(string $urlKey): PostInterface;

    public function getPost(): PostInterface;

    /**
     * @throws CouldNotDeleteException
     */
    public function delete(PostInterface $post): bool;

    /**
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $postId): bool;

    public function getTaggedPosts(int $tagId): Collection;

    public function getPostCollection(): Collection;

    public function getPostsByPage(int $page): Collection;

    /**
     * @throws NoSuchEntityException
     */
    public function getRecentPosts(): Collection;

    public function getActivePosts(?int $storeId = null): Collection;

    /**
     * @throws NoSuchEntityException
     */
    public function getFeaturedPosts(?int $storeId = null): Collection;

    public function getByIdAndStore(?int $postId, int $storeId = 0, bool $isAddDefaultStore = true): DataObject;
}
