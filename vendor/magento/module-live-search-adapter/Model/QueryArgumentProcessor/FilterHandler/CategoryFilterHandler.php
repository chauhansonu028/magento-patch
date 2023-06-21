<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model\QueryArgumentProcessor\FilterHandler;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\LiveSearchAdapter\Model\CategoryCache;
use Magento\Store\Model\StoreManagerInterface;

class CategoryFilterHandler implements FilterHandlerInterface
{
    /**
     * @var array
     */
    private array $filterValues;

    /**
     * @var CategoryRepositoryInterface
     */
    private CategoryRepositoryInterface $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var CategoryCache
     */
    private CategoryCache $categoryCache;

    /**
     * @var SearchCriteriaInterface
     */
    private SearchCriteriaInterface $searchCriteria;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @param array $filterValues
     * @param SearchCriteriaInterface $searchCriteria
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param CategoryCache $categoryCache
     * @param RequestInterface $request
     */
    public function __construct(
        array $filterValues,
        SearchCriteriaInterface $searchCriteria,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        CategoryCache $categoryCache,
        RequestInterface $request
    ) {
        $this->filterValues = $filterValues;
        $this->searchCriteria = $searchCriteria;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->categoryCache = $categoryCache;
        $this->request = $request;
    }

    /**
     * @inheritdoc
     */
    public function getFilterKey(): string
    {
        return 'categories';
    }

    /**
     * Get filter variables
     *
     * @return array|array[]
     * @throws NoSuchEntityException
     */
    public function getFilterVariables(): array
    {
        $categoryIds = array_unique($this->filterValues);
        if (empty($categoryIds)) {
            return [];
        }
        $categories = $this->getCategoryData($categoryIds);
        $allParentCategoryIds = array_column($categories, 'parent_id');
        $childCategoryIds = array_diff($categoryIds, $allParentCategoryIds);
        $requestParentCategoryIds = (count($categoryIds) === 1) ? $categoryIds :
            array_diff($categoryIds, $childCategoryIds);
        // if request is from category page
        if (count($requestParentCategoryIds) === 1 && $this->isRequestForCategoryPage()) {
            $childCategoryPaths = [];
            $filterVariables = [];
            foreach ($categories as $category) {
                if ($category['entity_id'] === $requestParentCategoryIds[0]) {
                    $filterVariables[] = ['attribute' => 'categoryPath', 'eq' => $category['path']];
                } else {
                    $childCategoryPaths[] = $category['path'];
                }
            }
            if (count($childCategoryPaths) > 0) {
                $filterVariables[] = [
                    'attribute' => $this->getFilterKey(), $this->getFilterType() => $childCategoryPaths
                ];
            }
            return $filterVariables;
        }

        return [
            [
                'attribute' => $this->getFilterKey(),
                $this->getFilterType() => array_column($categories, 'path')
            ]
        ];
    }

    /**
     * Is request for category page
     *
     * @return bool
     */
    private function isRequestForCategoryPage(): bool
    {
        // check for category page - REST
        if ($this->searchCriteria->getRequestName() === 'catalog_view_container'
            // check for category page - PWA (graphql)
            || (str_contains($this->searchCriteria->getRequestName(), 'graphql')
            && str_contains(strtolower($this->request->getParam('operationName', '')), 'categories'))
        ) {
            return true;
        }
        return false;
    }

    /**
     * Get category ids
     *
     * @param array $ids
     * @return array
     * @throws NoSuchEntityException
     */
    private function getCategoryData(array $ids): array
    {
        $storeId = $this->storeManager->getStore()->getId();
        $categoryIdsNotInCache = [];
        $categories = [];

        foreach ($ids as $id) {
            $key = $id . '_' . $storeId;
            $categoryData = $this->categoryCache->load($key);
            if ($categoryData) {
                $categories[] = $categoryData;
            } else {
                $categoryIdsNotInCache[] = $id;
            }
        }

        if (count($categoryIdsNotInCache) > 0) {
            foreach ($categoryIdsNotInCache as $categoryId) {
                $category = $this->categoryRepository->get($categoryId, $storeId);
                $category->getUrlInstance()->setScope($storeId);
                $categoryData = [
                    'entity_id' => $categoryId,
                    'parent_id' => $category->getParentId(),
                    'path' => $category->getUrlPath()
                ];
                $key = $categoryData['entity_id'] . '_' . $storeId;
                $this->categoryCache->save($key, $categoryData);
                $categories[] = $categoryData;
            }
        }

        return $categories;
    }

    /**
     * @inheritdoc
     */
    public function getFilterType(): string
    {
        return 'in';
    }
}
