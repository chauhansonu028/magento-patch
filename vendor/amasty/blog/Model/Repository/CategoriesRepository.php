<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Repository;

use Amasty\Blog\Api\CategoryRepositoryInterface;
use Amasty\Blog\Api\Data\CategoryInterface;
use Amasty\Blog\Api\Data\CategoryInterface as CatInterface;
use Amasty\Blog\Block\Sidebar\Category\TreeRenderer;
use Amasty\Blog\Helper\Settings;
use Amasty\Blog\Model\CategoriesFactory;
use Amasty\Blog\Model\ResourceModel\Categories as CategoriesResource;
use Amasty\Blog\Model\ResourceModel\Categories\Collection;
use Amasty\Blog\Model\ResourceModel\Categories\CollectionFactory;
use Amasty\Blog\Model\Source\CategoryStatus;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;

class CategoriesRepository implements CategoryRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CategoriesFactory
     */
    private $categoriesFactory;

    /**
     * @var CategoriesResource
     */
    private $categoriesResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $categoriess;

    /**
     * @var CollectionFactory
     */
    private $categoriesCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        CategoriesFactory $categoriesFactory,
        CategoriesResource $categoriesResource,
        CollectionFactory $categoriesCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->categoriesFactory = $categoriesFactory;
        $this->categoriesResource = $categoriesResource;
        $this->categoriesCollectionFactory = $categoriesCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @throws CouldNotSaveException
     */
    public function save(CatInterface $categories): CatInterface
    {
        try {
            if ($categories->getCategoryId()) {
                $categories = $this->getById($categories->getCategoryId())->addData($categories->getData());
            } else {
                $categories->setCategoryId(null);
            }
            $this->categoriesResource->save($categories);
            unset($this->categoriess[$categories->getCategoryId()]);
        } catch (\Exception $e) {
            if ($categories->getCategoryId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save categories with ID %1. Error: %2',
                        [$categories->getCategoryId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new categories. Error: %1', $e->getMessage()));
        }

        return $categories;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getById(int $categoryId): CatInterface
    {
        if (!isset($this->categoriess[$categoryId])) {
            /** @var \Amasty\Blog\Model\Categories $categories */
            $categories = $this->categoriesFactory->create();
            $this->categoriesResource->load($categories, $categoryId);

            if (!$categories->getCategoryId()) {
                throw new NoSuchEntityException(__('Categories with specified ID "%1" not found.', $categoryId));
            }

            $this->categoriess[$categoryId] = $categories;
        }

        return $this->categoriess[$categoryId];
    }

    public function getByUrlKey(?string $urlKey): CatInterface
    {
        return $this->getByUrlKeyAndStoreId($urlKey);
    }

    public function getByUrlKeyAndStoreId(?string $urlKey, ?int $storeId = Store::DEFAULT_STORE_ID): CatInterface
    {
        $collection = $this->getActiveCategories();
        $collection->addStoreFieldToFilter(CatInterface::URL_KEY, $urlKey);
        $collection->setLimit(1);
        /** @var CatInterface $categoryByUrlKey **/
        $categoryByUrlKey = $collection->getFirstItem();

        return $categoryByUrlKey;
    }

    /**
     * @throws CouldNotDeleteException
     */
    public function delete(CatInterface $categories): bool
    {
        try {
            $this->categoriesResource->delete($categories);
            unset($this->categoriess[$categories->getCategoryId()]);
        } catch (\Exception $e) {
            if ($categories->getCategoryId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove categories with ID %1. Error: %2',
                        [$categories->getCategoryId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove categories. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $categoryId): bool
    {
        $categoriesModel = $this->getById($categoryId);
        $this->delete($categoriesModel);

        return true;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\Blog\Model\ResourceModel\Categories\Collection $categoriesCollection */
        $categoriesCollection = $this->categoriesCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $categoriesCollection);
        }

        $searchResults->setTotalCount($categoriesCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $categoriesCollection);
        }

        $categoriesCollection->setCurPage($searchCriteria->getCurrentPage());
        $categoriesCollection->setPageSize($searchCriteria->getPageSize());

        $categories = [];
        /** @var CatInterface $categories */
        foreach ($categoriesCollection->getItems() as $categoryItem) {
            $categories[] = $this->getById($categoryItem->getCategoryId());
        }

        $searchResults->setItems($categories);

        return $searchResults;
    }

    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $categoriesCollection): void
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $categoriesCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    private function addOrderToCollection(array $sortOrders, Collection $categoriesCollection): void
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $categoriesCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }

    public function getAllCategories(): array
    {
        return $this->categoriesCollectionFactory->create()->addDefaultStore()->getItems();
    }

    public function getCategory(): CatInterface
    {
        return $this->categoriesFactory->create();
    }

    public function getStores(int $categoryId): array
    {
        return $this->categoriesResource->getStores($categoryId);
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getCategoriesByPost(int $postId): Collection
    {
        return $this->getActiveCategories()->addPostFilter($postId);
    }

    public function getCategoriesByIds(array $categoryIds = []): Collection
    {
        return $this->getActiveCategories()->addIdFilter($categoryIds);
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getActiveCategories(?int $storeId = null): Collection
    {
        $categories = $this->categoriesCollectionFactory->create();
        $categories->addStatusFilter(CategoryStatus::STATUS_ENABLED);
        $storeId = $storeId === null ? $this->storeManager->getStore()->getId() : $storeId;
        $categories->addStoreWithDefault((int)$storeId);

        return $categories;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getChildrenCategories(?int $parentId, int $limit = 0, ?int $storeId = null): Collection
    {
        $collection = $this->getActiveCategories($storeId);
        $collection->addFieldToFilter(CatInterface::PARENT_ID, $parentId ?? CategoryInterface::ROOT_CATEGORY_ID);
        if ($limit) {
            $collection->setPageSize($limit);
        }

        $collection->getSelect()->where('main_table.level <= ?', TreeRenderer::LEVEL_LIMIT);
        $collection->setSortOrder('asc');

        return $collection;
    }

    public function getByIdAndStore(?int $categoryId, ?int $storeId = 0, bool $isAddDefaultStore = true): CatInterface
    {
        $collection = $this->categoriesCollectionFactory->create();
        if ($isAddDefaultStore) {
            $collection->addStoreWithDefault((int)$storeId);
        } else {
            $collection->addStore($storeId);
        }

        $collection->addFieldToFilter(CatInterface::CATEGORY_ID, $categoryId);
        $collection->setLimit(1);

        return $collection->getFirstItem();
    }
}
