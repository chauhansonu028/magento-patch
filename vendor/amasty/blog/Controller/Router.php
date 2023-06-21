<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller;

use Amasty\Blog\Helper\Url;
use Amasty\Blog\Model\PageValidator;
use Amasty\Blog\Model\UrlResolver;

class Router implements \Magento\Framework\App\RouterInterface
{
    public const FLAG_REDIRECT = 'amplog_redirect_flag';

    public const BLOG_POST_URL = 'blogposts';

    public const MAX_REDIRECT_COUNT = 3;

    /** @var \Magento\Framework\App\ActionFactory */
    private $actionFactory;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var PageValidator
     */
    private $pageValidator;

    /**
     * @var \Amasty\Blog\Helper\Settings
     */
    private $settings;

    /**
     * @var \Amasty\Blog\Model\Router\Action
     */
    private $actionRouter;

    /**
     * @var \Magento\Theme\Block\Html\Pager
     */
    private $pager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieManager;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    /**
     * @var string
     */
    private $controlName = 'index';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        Url $url,
        PageValidator $pageValidator,
        \Amasty\Blog\Helper\Settings $settings,
        \Amasty\Blog\Model\Router\Action $actionRouter,
        \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieManager,
        \Magento\Theme\Block\Html\Pager $pager,
        UrlResolver $urlResolver,
        \Amasty\Blog\Model\ConfigProvider $configProvider
    ) {
        $this->actionFactory = $actionFactory;
        $this->url = $url;
        $this->pageValidator = $pageValidator;
        $this->settings = $settings;
        $this->actionRouter = $actionRouter;
        $this->pager = $pager;
        $this->cookieManager = $cookieManager;
        $this->urlResolver = $urlResolver;
        $this->configProvider = $configProvider;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return bool|\Magento\Framework\App\ActionInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $this->detectRedirect($request);

        # Result Action
        if ($this->actionRouter->getResult()) {
            # Redirect Flag
            if ($this->actionRouter->getIsRedirect()) {
                $this->redirectFlagUp();
            } else {
                $this->redirectFlagDown();
            }

            # Request Route
            $request->setModuleName($this->actionRouter->getModuleName())
                ->setControllerName($this->actionRouter->getControllerName())
                ->setActionName($this->actionRouter->getActionName());

            # Alias
            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $this->actionRouter->getAlias());

            # Transfer Params
            foreach ($this->actionRouter->getParams() as $key => $value) {
                $request->setParam($key, $value);
            }

            return $this->actionFactory->create(\Magento\Framework\App\Action\Forward::class);
        } else {
            return false;
        }
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function redirectFlagUp()
    {
        $flag = $this->getRedirectFlag();
        $value = $flag ? ++$flag : 1;
        $this->cookieManager->setPublicCookie(self::FLAG_REDIRECT, $value);

        return $this;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function redirectFlagDown()
    {
        $this->cookieManager->deleteCookie(self::FLAG_REDIRECT);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRedirectFlag()
    {
        return $this->cookieManager->getCookie(self::FLAG_REDIRECT);
    }

    /**
     * @param $request
     * @return void
     */
    private function detectRedirect($request)
    {
        $identifier = $this->getIdentifier($request);
        $wrongPage = $request->getParam($this->pager->getPageVarName()) ?: 1;
        $page = $request->getParam($this->pager->getPageVarName(), false);
        $page = $page ? (int) $page : false;

        if (!$this->isValidBlogRoute($identifier)) {
            $this->actionRouter->setResult(false);
            return;
        }

        if ($this->pageValidator->isNeedRedirectToPageWithPostfix($identifier)
            && !$this->isBlogPostsRedirect($identifier)
        ) {
            $this->fillActionRouter(
                true,
                $identifier,
                'redirect',
                'url',
                $this->urlResolver->getUrlWithPostfix($identifier)
            );
        } elseif ($postId = $this->pageValidator->getPostId($identifier)) {
            $this->postRedirect($postId, $identifier);
        } elseif ($categoryId = $this->pageValidator->getCategoryId($identifier, $page)) {
            $this->categoryRedirect($categoryId, $identifier, $page, $wrongPage);
        } elseif ($tagId = $this->pageValidator->getTagId($identifier, $page)) {
            $this->tagRedirect($tagId, $identifier, $page, $wrongPage);
        } elseif ($this->pageValidator->isIndexRequest($identifier, $page)) {
            $this->blogRedirect($identifier, $page, $wrongPage);
        } elseif ($authorId = $this->pageValidator->getAuthorId($identifier, $page)) {
            $this->authorRedirect($authorId, $identifier, $page, $wrongPage);
        } elseif ($this->pageValidator->getIsSearchRequest($identifier, $page)) {
            $this->fillActionRouter(false, $identifier, 'search', $this->pager->getPageVarName(), $page);
        } elseif ($this->isBlogPostsRedirect($identifier)) {
            $identifier = $this->clearUrl($identifier);
            $this->fillActionRouter(false, $identifier, 'account', $this->pager->getPageVarName(), $page);
        }
    }

    private function clearUrl(string $identifier): string
    {
        return explode('?', $identifier)[0];
    }

    private function isBlogPostsRedirect(string $identifier): bool
    {
        $getParamSymbolPos = strpos($identifier, '?');

        if ($getParamSymbolPos) {
            $identifier = substr($identifier, 0, $getParamSymbolPos);
        }

        return str_replace('/', '', $identifier) == self::BLOG_POST_URL;
    }

    private function isValidBlogRoute(string $identifier): bool
    {
        $getParamSymbolPos = strpos($identifier, '?');

        if ($getParamSymbolPos) {
            $identifier = substr($identifier, 0, $getParamSymbolPos);
        }

        $postfix = $this->settings->getBlogPostfix();
        if (strlen($postfix) > 1) {
            $identifier = str_replace($postfix, '', $identifier);
        }

        $pathParts = explode('/', $identifier);

        return in_array($pathParts[0], [$this->settings->getSeoRoute(), self::BLOG_POST_URL]);
    }

    /**
     * @param $request
     * @return false|string
     */
    private function getIdentifier($request)
    {
        $identifier = ltrim($request->getRequestString(), '/');

        $this->replaceAmp($identifier);

        return $identifier;
    }

    /**
     * @param $identifier
     */
    private function replaceAmp(&$identifier)
    {
        $route = $this->settings->getSeoRoute();

        if ($this->configProvider->isAmpEnabled() && strpos($identifier, 'amp/' . $route) !== false) {
            $this->controlName = 'amp';
            $identifier = str_replace('amp/', '', $identifier);
        }
    }

    /**
     * @param $isRedirect
     * @param $flag
     * @param $action
     * @param $keyParam
     * @param $param
     */
    private function fillActionRouter($isRedirect, $flag, $action, $keyParam, $param)
    {
        $this->actionRouter->setIsRedirect($isRedirect)
            ->setRedirectFlag($flag)
            ->setModuleName('amblog')
            ->setControllerName($this->controlName)
            ->setActionName($action)
            ->setParam($keyParam, $param)
            ->setAlias($flag)
            ->setResult(true);
    }

    /**
     * @param $postId
     * @param $identifier
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function postRedirect($postId, $identifier)
    {
        if ($postId
            && !$this->pageValidator->isRightPostSyntax($identifier, $postId)
            && ($this->getRedirectFlag() < self::MAX_REDIRECT_COUNT)
        ) {
            $this->fillActionRouter(
                true,
                $identifier,
                'redirect',
                'url',
                $this->urlResolver->getPostUrlByIdAndStore($postId)
            );
        } else {
            $this->fillActionRouter(false, $identifier, 'post', 'id', $postId);
        }
    }

    /**
     * @param $categoryId
     * @param $identifier
     * @param $page
     * @param $wrongPage
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function categoryRedirect($categoryId, $identifier, $page, $wrongPage)
    {
        $isRightSyntax = $this->pageValidator->isRightCategorySyntax(
            $identifier,
            $categoryId,
            $page ? $page : $wrongPage
        );
        if ($categoryId && !$isRightSyntax && ($this->getRedirectFlag() < self::MAX_REDIRECT_COUNT)) {
            $url = $this->urlResolver->getCategoryUrlById(
                $categoryId,
                $wrongPage ? $wrongPage : $page
            );
            $this->fillActionRouter(true, $identifier, 'redirect', 'url', $url);
        } else {
            $this->fillActionRouter(false, $identifier, 'category', 'id', $categoryId);
            $this->actionRouter->setParam($this->pager->getPageVarName(), $page);
        }
    }

    /**
     * @param $tagId
     * @param $identifier
     * @param $page
     * @param $wrongPage
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function tagRedirect($tagId, $identifier, $page, $wrongPage)
    {
        $isRightSyntax = $this->pageValidator->isRightTagSyntax(
            $identifier,
            $tagId,
            $page ? $page : $wrongPage
        );
        if ($tagId && !$isRightSyntax && ($this->getRedirectFlag() < self::MAX_REDIRECT_COUNT)) {
            $url = $this->urlResolver->getTagUrlById($tagId, $wrongPage ? $wrongPage : $page);
            $this->fillActionRouter(true, $identifier, 'redirect', 'url', $url);
        } else {
            $this->fillActionRouter(false, $identifier, 'tag', 'id', $tagId);
            $this->actionRouter->setParam($this->pager->getPageVarName(), $page);
        }
    }

    /**
     * @param $urlKey
     * @param $identifier
     * @param $page
     * @param $wrongPage
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function authorRedirect($authorId, $identifier, $page, $wrongPage)
    {
        $isRightSyntax = $this->pageValidator->isRightAuthorSyntax(
            $identifier,
            $authorId,
            $page ? $page : $wrongPage
        );
        if ($authorId && !$isRightSyntax && ($this->getRedirectFlag() < self::MAX_REDIRECT_COUNT)) {
            $url = $this->urlResolver->getAuthorUrlById($authorId, $wrongPage ?: $page);
            $this->fillActionRouter(true, $identifier, 'redirect', 'url', $url);
        } else {
            $this->fillActionRouter(false, $identifier, 'author', 'id', $authorId);
            $this->actionRouter->setParam($this->pager->getPageVarName(), $page);
        }
    }

    /**
     * @param $identifier
     * @param $page
     * @param $wrongPage
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function blogRedirect($identifier, $page, $wrongPage)
    {
        $isRightSyntax = $this->pageValidator->isRightBlogSyntax($identifier, $page ?: $wrongPage);
        if ($this->pageValidator->isIndexRequest($identifier, $page)
            && !$isRightSyntax
            && ($this->getRedirectFlag() < self::MAX_REDIRECT_COUNT)
        ) {
            $url = $this->urlResolver->getBlogUrl($wrongPage ?: $page);
            $this->fillActionRouter(true, $identifier, 'redirect', 'url', $url);
        } else {
            $this->fillActionRouter(false, $identifier, 'index', $this->pager->getPageVarName(), $page);
        }
    }
}
