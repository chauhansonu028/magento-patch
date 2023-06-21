<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model;

use Amasty\Blog\Api\AuthorRepositoryInterface;
use Amasty\Blog\Api\CategoryRepositoryInterface;
use Amasty\Blog\Api\PostRepositoryInterface;
use Amasty\Blog\Api\TagRepositoryInterface;
use Amasty\Blog\Controller\AbstractController\Search;
use Amasty\Blog\Helper\Settings;
use Amasty\Blog\Helper\Url;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

class UrlResolver
{
    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * @var AuthorRepositoryInterface
     */
    private $authorRepository;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        PostRepositoryInterface $postRepository,
        CategoryRepositoryInterface $categoryRepository,
        TagRepositoryInterface $tagRepository,
        AuthorRepositoryInterface $authorRepository,
        Settings $settings,
        StoreManagerInterface $storeManager,
        ConfigProvider $configProvider,
        RequestInterface $request
    ) {
        $this->tagRepository = $tagRepository;
        $this->categoryRepository = $categoryRepository;
        $this->postRepository = $postRepository;
        $this->authorRepository = $authorRepository;
        $this->settings = $settings;
        $this->storeManager = $storeManager;
        $this->configProvider = $configProvider;
        $this->request = $request;
    }

    /**
     * @param $id
     * @return string
     */
    public function getPostUrlById($id)
    {
        try {
            $url = $this->postRepository->getById($id)->getUrl();
        } catch (NoSuchEntityException $e) {
            $url = '';
        }

        return $url;
    }

    public function getPostUrlByIdAndStore(int $id, int $storeId = null): string
    {
        try {
            $storeId = $storeId ?: $this->storeManager->getStore()->getId();
            $url = $this->postRepository->getByIdAndStore($id, $storeId)->getUrl();
        } catch (NoSuchEntityException $e) {
            $url = '';
        }

        return $url;
    }

    /**
     * @param $id
     * @param $page
     * @return string
     */
    public function getCategoryUrlById($id, $page = 1)
    {
        try {
            $category = $this->categoryRepository->getByIdAndStore(
                $id,
                (int) $this->storeManager->getStore()->getId()
            );
            $url = $category->getUrl($page);
        } catch (NoSuchEntityException $e) {
            $url = '';
        }

        return $url;
    }

    /**
     * @param $id
     * @param $page
     * @return string
     */
    public function getTagUrlById($id, $page = 1)
    {
        try {
            $tag = $this->tagRepository->getByIdAndStore(
                $id,
                (int) $this->storeManager->getStore()->getId()
            );
            $url = $tag->getUrl($page);
        } catch (NoSuchEntityException $e) {
            $url = '';
        }

        return $url;
    }

    /**
     * @param $id
     * @param $page
     * @return string
     */
    public function getAuthorUrlById($id, $page = 1)
    {
        try {
            $author = $this->authorRepository->getByIdAndStore(
                $id,
                (int) $this->storeManager->getStore()->getId()
            );
            $url = $author->getUrl($page);
        } catch (NoSuchEntityException $e) {
            $url = '';
        }

        return $url;
    }

    public function getSearchPageUrl($page = 1, ?StoreInterface $store = null): string
    {
        $store = $store ?: $this->storeManager->getStore();
        return $this->getBaseUrl($store)
            . $this->configProvider->getSeoRoute($store)
            . '/' . Url::ROUTE_SEARCH
            . $this->getUrlPostfix($page);
    }

    public function getSearchPageUrlWithoutPostfix(): string
    {
        $store = $this->storeManager->getStore();

        return $this->getBaseUrl($store)
            . $this->configProvider->getSeoRoute($store)
            . '/' . Url::ROUTE_SEARCH;
    }

    public function getSearchPageUrlWithQuery($page = 1, ?StoreInterface $store = null): string
    {
        $url = $this->getSearchPageUrl($page, $store);
        $delimiter = '';
        if ($this->getQuery()) {
            $delimiter = strpos($url, '?') === false ? '?' : '&';
            $delimiter .= Search::SEARCH_PARAM . '=';
        }

        return $url . $delimiter . $this->getQuery();
    }

    public function getQuery(): string
    {
        return (string) $this->request->getParam(Search::SEARCH_PARAM, '');
    }

    /**
     * @param int $page
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBlogUrl($page = 1, ?StoreInterface $store = null)
    {
        $store = $store ?: $this->storeManager->getStore();
        return $this->getBaseUrl($store)
            . $this->configProvider->getSeoRoute($store)
            . $this->getUrlPostfix($page);
    }

    private function getBaseUrl(StoreInterface $store): string
    {
        return $store->getBaseUrl();
    }

    private function getUrlPostfix($page = 1, ?StoreInterface $store = null): string
    {
        $postfix = $this->configProvider->getBlogPostfix($store);

        return $page > 1 ? "{$postfix}?p={$page}" : $postfix;
    }

    public function getUrlWithPostfix(string $identifierWithoutPostfix, ?StoreInterface $store = null): string
    {
        $urlParts = explode('?', $identifierWithoutPostfix);
        $paramsPart = !empty($urlParts[1]) ? '?' . $urlParts[1] : '';
        $routePath = ltrim($urlParts[0], '/');
        $store = $store ?: $this->storeManager->getStore();
        $baseUrl = rtrim($this->getBaseUrl($store), '/');
        $postfix = $this->configProvider->getBlogPostfix($store);

        return sprintf('%s/%s%s%s', $baseUrl, $routePath, $postfix, $paramsPart);
    }
}
