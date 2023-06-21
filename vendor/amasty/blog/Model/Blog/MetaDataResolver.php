<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Blog;

use Amasty\Blog\Model\AbstractModel;
use Amasty\Blog\Model\ConfigProvider;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\Page as ResultPage;

class MetaDataResolver
{
    /**
     * @var StringUtils
     */
    private $string;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        StringUtils $string,
        ConfigProvider $configProvider,
        Registry $registry,
        UrlInterface $urlBuilder
    ) {
        $this->string = $string;
        $this->configProvider = $configProvider;
        $this->registry = $registry;
        $this->urlBuilder = $urlBuilder;
    }

    public function preparePageMetadata(
        ResultPage $resultPage,
        string $metaTitle,
        string $keyword,
        string $description,
        string $url,
        string $title,
        ?string $metaRobots = null
    ): void {
        $pageLayout = $resultPage->getLayout();
        $pageConfig = $resultPage->getConfig();
        $metaTitle = $metaTitle ?: $title;
        $pageConfig->setMetaTitle($this->modifyMetaTitle($metaTitle));
        $pageConfig->getTitle()->set($metaTitle);

        if ($keyword) {
            $pageConfig->setKeywords($keyword);
        }

        if ($description) {
            $pageConfig->setDescription($description);
        }

        if ($url) {
            $pageConfig->addRemotePageAsset(
                $this->getCanonicalUrl($url),
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
        }

        if ($metaRobots) {
            $pageConfig->setMetadata('robots', $metaRobots);
        }

        $pageMainTitle = $pageLayout->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($title);
        }
    }

    private function getCanonicalUrl(string $url): string
    {
        $post = $this->registry->registry(Registry::CURRENT_POST);
        if ($post && $post->getCanonicalUrl()) {
            $url = $this->urlBuilder->getBaseUrl() . trim($post->getCanonicalUrl(), '/');
        }

        return $url;
    }

    private function modifyMetaTitle(string $metaTitle): string
    {
        $prefix = $this->configProvider->getTitlePrefix();
        if ($prefix) {
            $metaTitle = $prefix . ' - ' . $metaTitle;
        }

        $suffix = $this->configProvider->getTitleSuffix();
        if ($suffix) {
            $metaTitle .= ' | ' . $suffix;
        }

        return $metaTitle;
    }

    public function cutDescription(string $description): string
    {
        return $this->string->substr(strip_tags($description), 0, 255);
    }

    public function getMetaRobots(?AbstractModel $model = null): string
    {
        $metaRobotsFromConfig = $this->configProvider->getMetaRobots();
        if (!$model) {
            return $metaRobotsFromConfig;
        }

        $metaRobots = $model->getData('meta_robots') ?? $metaRobotsFromConfig;

        return (string)$metaRobots;
    }
}
