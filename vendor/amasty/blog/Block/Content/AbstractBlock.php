<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Content;

use Amasty\Blog\Block\Content\Post\Details;
use Amasty\Blog\Helper\Data;
use Amasty\Blog\Helper\Date;
use Amasty\Blog\Helper\Settings;
use Amasty\Blog\Helper\Url;
use Amasty\Blog\Model\ConfigProvider;
use Amasty\Blog\Model\UrlResolver;
use Amasty\Blog\ViewModel\Author\SmallImage;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class AbstractBlock extends Template
{
    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var Url
     */
    private $urlHelper;

    /**
     * @var Settings
     */
    private $settingsHelper;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var Date
     */
    private $helperDate;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    /**
     * @var array
     */
    private $crumbs = [];

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var SmallImage
     */
    private $smallImage;

    public function __construct(
        Context $context,
        Data $dataHelper,
        Settings $settingsHelper,
        Url $urlHelper,
        UrlResolver $urlResolver,
        Date $helperDate,
        ConfigProvider $configProvider,
        SmallImage $smallImage,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->urlHelper = $urlHelper;
        $this->settingsHelper = $settingsHelper;
        $this->context = $context;
        $this->helperDate = $helperDate;
        $this->urlResolver = $urlResolver;
        $this->configProvider = $configProvider;
        $this->smallImage = $smallImage;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepareBreadcrumbs()
    {
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $this->addCrumb(
                $breadcrumbs,
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Home'),
                    'link'  => $this->getBaseUrl()
                ]
            );
            $this->addCrumb(
                $breadcrumbs,
                'blog',
                [
                    'label' => $this->getSettingHelper()->getBreadcrumb(),
                    'title' => $this->getSettingHelper()->getBreadcrumb(),
                    'link'  => $this->getUrlResolverModel()->getBlogUrl(),
                ]
            );
        }

        return $this;
    }

    /**
     * @param \Magento\Theme\Block\Html\Breadcrumbs $block
     * @param $key
     * @param $data
     */
    protected function addCrumb(\Magento\Theme\Block\Html\Breadcrumbs $block, $key, $data)
    {
        $this->crumbs[$key] = $data;
        $block->addCrumb($key, $data);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->prepareBreadcrumbs();

        return $this;
    }

    /**
     * @param null $post
     * @return bool|string
     */
    public function getAmpHeaderHtml($post = null)
    {
        return $this->getHtml(
            Details::class,
            'Amasty_Blog::amp/post/header.phtml',
            $post
        );
    }

    /**
     * @param null $post
     * @return bool|string
     */
    public function getAuthorHtml($post = null)
    {
        return $this->getHtml(
            Details::class,
            'Amasty_Blog::post/author.phtml',
            $post,
            ['view_model' => $this->smallImage]
        );
    }

    /**
     * @param null $post
     * @return bool|string
     */
    public function getShortCommentsHtml($post = null)
    {
        return $this->getHtml(
            Details::class,
            'Amasty_Blog::post/short_comments.phtml',
            $post
        );
    }

    /**
     * @param $post
     * @param bool $isAmp
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoriesHtml($post = null, $isAmp = false)
    {
        $html = '';
        if ($this->settingsHelper->getUseCategories()) {
            $template = $isAmp ? 'Amasty_Blog::amp/list/categories.phtml' : 'Amasty_Blog::list/categories.phtml';

            $html = $this->getHtml(Details::class, $template, $post);
        }

        return $html;
    }

    /**
     * @param $post
     * @param bool $isAmp
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTagsHtml($post = null, $isAmp = false)
    {
        $html = '';
        if ($this->settingsHelper->getUseTags()) {
            $template = $isAmp ? 'Amasty_Blog::amp/list/tags.phtml' : 'Amasty_Blog::list/tags.phtml';
            $html = $this->getHtml(Details::class, $template, $post);
        }

        return $html;
    }

    /**
     * @param $blockClass
     * @param $template
     * @param $post
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getHtml($blockClass, $template, $post, array $arguments = [])
    {
        $block = $this->getLayout()->createBlock($blockClass);
        $html = '';
        if ($block) {
            $block->setData($arguments);
            $block->setPost($post)->setTemplate($template);
            $html = $block->toHtml();
        }

        return $html;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return Url
     */
    public function getUrlHelper()
    {
        return $this->urlHelper;
    }

    /**
     * @return UrlResolver
     */
    public function getUrlResolverModel()
    {
        return $this->urlResolver;
    }

    /**
     * @return Settings
     */
    public function getSettingHelper()
    {
        return $this->settingsHelper;
    }

    public function renderDate(string $datetime, bool $isEditedAt = false): string
    {
        return $this->helperDate->renderDate($datetime, false, false, $isEditedAt);
    }

    /**
     * @return array
     */
    public function getCrumbs()
    {
        return $this->crumbs;
    }

    public function isHumanized(): bool
    {
        return $this->configProvider->getDateFormat() === Date::DATE_TIME_PASSED;
    }

    public function isShowEditedAt(): bool
    {
        return $this->configProvider->isShowEditedAt();
    }

    public function isHumanizedEditedAt(): bool
    {
        return $this->configProvider->getEditedAtDateFormat() === Date::DATE_TIME_PASSED;
    }

    public function getReadTime(string $content): int
    {
        return $this->dataHelper->getReadTime($content);
    }

    public function isDisplayReadTime(): bool
    {
        return $this->configProvider->isDisplayReadTime();
    }
}
