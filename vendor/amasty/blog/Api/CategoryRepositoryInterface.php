<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Api;

use Amasty\Blog\Api\Data\CategoryInterface as CatInterface;
use Amasty\Blog\Model\ResourceModel\Categories\Collection;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;

/**
 * @api
 */
interface CategoryRepositoryInterface
{
    public function save(CatInterface $category): CatInterface;

    /**
     * @throws NoSuchEntityException
     */
    public function getById(int $categoryId): CatInterface;

    /**
     * @deprecared since version 2.7.0 Now url key can be configured by store view
     * @see \Amasty\Blog\Api\CategoryRepositoryInterface::getByUrlKeyAndStoreId
     */
    public function getByUrlKey(?string $urlKey): CatInterface;

    public function getByUrlKeyAndStoreId(?string $urlKey, ?int $storeId = Store::DEFAULT_STORE_ID): CatInterface;

    public function getCategory(): CatInterface;

    /**
     * @throws CouldNotDeleteException
     */
    public function delete(CatInterface $category): bool;

    /**
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $categoryId): bool;

    /**
     * @throws NoSuchEntityException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;

    public function getAllCategories(): array;

    public function getStores(int $categoryId): array;

    public function getCategoriesByPost(int $postId): Collection;

    public function getActiveCategories(?int $storeId = null): Collection;

    public function getCategoriesByIds(array $categoryIds = []): Collection;

    /**
     * @throws NoSuchEntityException
     */
    public function getChildrenCategories(int $parentId, int $limit = 0, ?int $storeId = null): Collection;

    public function getByIdAndStore(?int $categoryId, ?int $storeId = 0, bool $isAddDefaultStore = true): CatInterface;
}
