<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Plugin\Store\ViewModel\SwitcherUrlProvider;

use Amasty\Blog\Api\AuthorRepositoryInterface;
use Amasty\Blog\Api\CategoryRepositoryInterface;
use Amasty\Blog\Api\PostRepositoryInterface;
use Amasty\Blog\Api\TagRepositoryInterface;
use Amasty\Blog\Model\Blog\Registry as BlogRegistry;
use Amasty\Blog\Model\UrlResolver;
use Laminas\Uri\Http;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\ViewModel\SwitcherUrlProvider as SwitcherUrl;

class ReplaceStoreSwitcherUrlForBlogEntities
{
    private const STORE_PARAM_NAME = '___store';
    private const FROM_STORE_PARAM_NAME = '___from_store';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Http
     */
    private $http;

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
     * @var BlogRegistry
     */
    private $registry;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        EncoderInterface $encoder,
        StoreManagerInterface $storeManager,
        Http $http,
        BlogRegistry $registry,
        CategoryRepositoryInterface $categoryRepository,
        TagRepositoryInterface $tagRepository,
        AuthorRepositoryInterface $authorRepository,
        UrlResolver $urlResolver,
        PostRepositoryInterface $postRepository = null
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->encoder = $encoder;
        $this->storeManager = $storeManager;
        $this->http = $http;
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository = $tagRepository;
        $this->authorRepository = $authorRepository;
        $this->registry = $registry;
        $this->urlResolver = $urlResolver;
        $this->postRepository = $postRepository ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(PostRepositoryInterface::class);
    }

    /**
     * @phpstan-ignore-next-line
     *
     * @param SwitcherUrl $subject
     * @param callable $proceed
     * @param Store $store
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGetTargetStoreRedirectUrl(SwitcherUrl $subject, callable $proceed, Store $store): string
    {
        if ($this->request->getModuleName() == 'amblog') {
            $params = [
                self::STORE_PARAM_NAME => $store->getCode(),
                self::FROM_STORE_PARAM_NAME => $this->storeManager->getStore()->getCode(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->encoder->encode($this->getSwitcherUrl($store)),
            ];
            $url = $this->urlBuilder->getUrl('stores/store/redirect', $params);
        } else {
            $url = $proceed($store);
        }

        return $url;
    }

    private function getSwitcherUrl(StoreInterface $newStore): string
    {
        $skipClearUrl = false;
        switch (true) {
            case $this->registry->registry(BlogRegistry::CURRENT_AUTHOR):
                $entity = $this->registry->registry(BlogRegistry::CURRENT_AUTHOR);
                $entity = $this->authorRepository->getByIdAndStore((int) $entity->getId(), (int) $newStore->getId());
                $url = $entity->getUrl(1, $newStore);
                break;
            case $this->registry->registry(BlogRegistry::CURRENT_CATEGORY):
                $entity = $this->registry->registry(BlogRegistry::CURRENT_CATEGORY);
                $entity = $this->categoryRepository->getByIdAndStore((int) $entity->getId(), (int) $newStore->getId());
                $url = $entity->getUrl(1, $newStore);
                break;
            case $this->registry->registry(BlogRegistry::CURRENT_TAG):
                $entity = $this->registry->registry(BlogRegistry::CURRENT_TAG);
                $entity = $this->tagRepository->getByIdAndStore((int) $entity->getId(), (int) $newStore->getId());
                $url = $entity->getUrl(1, $newStore);
                break;
            case $this->registry->registry(BlogRegistry::CURRENT_POST):
                $entity = $this->registry->registry(BlogRegistry::CURRENT_POST);
                $entity = $this->postRepository->getByIdAndStore((int)$entity->getId(), (int)$newStore->getId());
                $url = $entity->getUrl(1, $newStore);
                break;
            case $this->registry->registry(BlogRegistry::INDEX_PAGE):
                $url = $this->urlResolver->getBlogUrl(1, $newStore);
                break;
            case $this->registry->registry(BlogRegistry::SEARCH_PAGE):
                $url = $this->urlResolver->getSearchPageUrlWithQuery(1, $newStore);
                $skipClearUrl = true;
                break;
            default:
                $url = $this->getCurrentUrl($newStore);
        }

        return $skipClearUrl ? $url : $this->removeParamsFromUrl($url);
    }

    private function removeParamsFromUrl(string $url): string
    {
        $uri = $this->http->parse($url);

        return $uri->getScheme() . '://' . $uri->getHost() . $uri->getPath();
    }

    private function getCurrentUrl(StoreInterface $store): string
    {
        return $store->getCurrentUrl(false);
    }
}
