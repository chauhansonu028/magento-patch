<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Observer;

use Amasty\Blog\Model\ConfigProvider;
use Amasty\Blog\Model\Detection\MobileDetection;
use Amasty\Blog\Model\Layout\Config;
use Amasty\Blog\Model\Layout\Config\PageTypeRelatedModifier;
use Amasty\Blog\Model\Layout\ConfigFactory;
use Amasty\Blog\Model\Layout\GeneratorInterface;
use Amasty\Blog\Model\Layout\LayoutUpdateNameGenerator;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;

class ApplyBlogLayout implements ObserverInterface
{
    const MOBILE = 'mobile';
    const DESKTOP = 'desktop';
    const ROUTE_POST = 'post';
    const ROUTE_LIST = 'list';
    const LISTING_UPDATE = 'amasty_blog_listing_pages';
    const POST_UPDATE = 'amasty_blog_post_page';

    /**
     * @var MobileDetection
     */
    private $mobileDetection;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ConfigFactory
     */
    private $layoutConfigFactory;

    /**
     * @var string[]
     */
    private $blogRoutes = [];

    /**
     * @var LayoutUpdateNameGenerator
     */
    private $layoutUpdateNameGenerator;

    /**
     * @var GeneratorInterface
     */
    private $xmlGenerator;

    /**
     * @var PageTypeRelatedModifier
     */
    private $pageTypeRelatedModifier;

    public function __construct(
        MobileDetection $mobileDetection,
        RequestInterface $request,
        ConfigProvider $configProvider,
        ConfigFactory $layoutConfigFactory,
        LayoutUpdateNameGenerator $layoutUpdateNameGenerator,
        GeneratorInterface $xmlGenerator,
        PageTypeRelatedModifier $pageTypeRelatedModifier,
        array $blogRoutes = []
    ) {
        $this->mobileDetection = $mobileDetection;
        $this->request = $request;
        $this->configProvider = $configProvider;
        $this->layoutConfigFactory = $layoutConfigFactory;
        $this->blogRoutes = array_merge($this->blogRoutes, $blogRoutes);
        $this->layoutUpdateNameGenerator = $layoutUpdateNameGenerator;
        $this->xmlGenerator = $xmlGenerator;
        $this->pageTypeRelatedModifier = $pageTypeRelatedModifier;
    }

    public function execute(Observer $observer): void
    {
        /** @var LayoutInterface|null $layout **/
        $layout = $observer->getEvent()->getLayout();
        $fullActionName = $observer->getEvent()->getFullActionName();

        if (null !== $layout && in_array($fullActionName, $this->blogRoutes)) {
            $routeIdentifier = $this->getRouteIdentifier();
            $layoutConfig = $this->generateLayoutConfig($routeIdentifier, $fullActionName);
            $layoutUpdate = $layout->getUpdate();
            $layoutXml = $this->xmlGenerator->generate($layoutConfig);
            $layoutUpdate->addUpdate($layoutXml);
            $layoutUpdate->addHandle($layoutConfig->getLayoutName());
            $layoutUpdate->addHandle($this->getLayoutHandleName($routeIdentifier));
            $layoutUpdate->addHandle($this->getPageTypeUpdate());
        }
    }

    private function getLayoutHandleName(string $identifier): string
    {
        return $this->layoutUpdateNameGenerator->generate($identifier);
    }

    private function getRouteIdentifier(): string
    {
        $platformCode = $this->mobileDetection->isMobile() ? self::MOBILE : self::DESKTOP;
        $routeCode = $this->getRouteCode();

        return sprintf('%s_%s', $platformCode, $routeCode);
    }

    public function getRouteCode(): string
    {
        return in_array($this->request->getActionName(), ['post', 'preview'])
            ? self::ROUTE_POST
            : self::ROUTE_LIST;
    }

    private function getPageTypeUpdate(): string
    {
        return $this->getRouteCode() === self::ROUTE_LIST
            ? self::LISTING_UPDATE
            : self::POST_UPDATE;
    }

    private function generateLayoutConfig(string $routeIdentifier, string $fullActionName): Config
    {
        $currentLayoutConfiguration = $this->configProvider->getLayoutConfigByIdentifier($routeIdentifier);
        $currentLayoutConfiguration = $this->pageTypeRelatedModifier->modify(
            $fullActionName,
            $currentLayoutConfiguration
        );
        $layoutConfig = $this->layoutConfigFactory->fromJsonConfig($currentLayoutConfiguration, $routeIdentifier);
        $layoutConfig->addCacheKeyInfo($fullActionName);

        return $layoutConfig;
    }
}
