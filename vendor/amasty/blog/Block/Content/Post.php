<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Content;

use Amasty\Blog\Api\PostRepositoryInterface;
use Amasty\Blog\Helper\Data;
use Amasty\Blog\Helper\Date;
use Amasty\Blog\Helper\Settings;
use Amasty\Blog\Helper\Url;
use Amasty\Blog\Model\Blog\Registry;
use Amasty\Blog\Model\ConfigProvider;
use Amasty\Blog\Model\NetworksFactory;
use Amasty\Blog\Model\Posts;
use Amasty\Blog\Model\UrlResolver;
use Amasty\Blog\ViewModel\Author\SmallImage;
use Magento\Catalog\Model\Session;
use Magento\Cms\Model\Template\Filter;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template\Context;

class Post extends AbstractBlock implements IdentityInterface
{
    /**
     * @var \Amasty\Blog\Model\PostsFactory
     */
    private $postRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var NetworksFactory
     */
    private $networksModel;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var Session
     */
    private $session;

    public function __construct(
        Context $context,
        Data $dataHelper,
        Registry $registry,
        Settings $settingsHelper,
        Url $urlHelper,
        Filter $filter,
        PostRepositoryInterface $postRepository,
        NetworksFactory $networksModel,
        UrlResolver $urlResolver,
        Date $helperDate,
        EncoderInterface $jsonEncoder,
        Session $session,
        ConfigProvider $configProvider,
        SmallImage $smallImage,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $dataHelper,
            $settingsHelper,
            $urlHelper,
            $urlResolver,
            $helperDate,
            $configProvider,
            $smallImage,
            $data
        );
        $this->postRepository = $postRepository;
        $this->registry = $registry;
        $this->filter = $filter;
        $this->networksModel = $networksModel;
        $this->jsonEncoder = $jsonEncoder;
        $this->session = $session;
    }

    /**
     * @return Posts|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPost()
    {
        $post = $this->registry->registry(Registry::CURRENT_POST);
        if (!$post) {
            $this->session->start();
            $postId = $this->getRequest()->getParam('id') ?: $this->session->getCurrentPostId();
            if ($postId) {
                $post = $this->postRepository->getById((int)$postId);
                $this->registry->register(Registry::CURRENT_POST, $post, true);
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__('Unknown post id.'));
            }
        }

        return $post;
    }

    /**
     * @return array
     */
    public function getJsonMicroData()
    {
        $resultArray = [$this->jsonEncoder->encode($this->generateMainMicroData())];

        $breadCrumbItems = $this->getBreadCrumbData();
        if ($breadCrumbItems) {
            $resultArray[] = $this->jsonEncoder->encode(
                [
                    '@context'        => 'http://schema.org',
                    '@type'           => 'BreadcrumbList',
                    'itemListElement' => $breadCrumbItems
                ]
            );
        }

        return $resultArray;
    }

    /**
     * @return array
     */
    private function generateMainMicroData()
    {
        $main = [
            '@context'      => 'http://schema.org',
            '@type'         => 'BlogPosting',
            'author'        => [
                "@type" => 'Person',
                "name"  => $this->escapeHtml($this->getPost()->getPostedBy() ?: 'undefined')
            ],
            'datePublished' => $this->escapeHtml($this->getPost()->getPublishedAt()),
            'dateModified'  => $this->escapeHtml($this->getPost()->getUpdatedAt()),
            'name'          => $this->escapeHtml($this->getPost()->getTitle()),
            'description'   => $this->escapeHtml($this->getPost()->getShortContent()),
            'image'         => $this->getPost()->getPostImageSrc(),
            'mainEntityOfPage'
                            => $this->escapeUrl($this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true])),
            'headline'      => $this->escapeHtml($this->getPost()->getTitle())
        ];

        $orgName = $this->getSettingHelper()->getModuleConfig('search_engine/organization_name');
        if ($orgName) {
            $main['publisher'] = [
                "@type" => 'Organization',
                'url'   => $this->_urlBuilder->getBaseUrl(),
                "name"  => $this->escapeHtml($orgName)
            ];

            $logoBlock = $this->getLayout()->getBlock('logo');
            if ($logoBlock) {
                $main['publisher']['logo'] = $logoBlock = $logoBlock->getLogoSrc();
            }
        }

        return $main;
    }

    /**
     * @return array
     */
    private function getBreadCrumbData()
    {
        $items = [];
        $position = 0;
        $breadcrumbs = $this->getCrumbs();
        foreach ($breadcrumbs as $breadcrumb) {
            if (!isset($breadcrumb['link']) || !$breadcrumb['link']) {
                continue;
            }

            $items []= [
                '@type' => 'ListItem',
                'position' => ++$position,
                'item' => [
                    '@id' => $breadcrumb['link'],
                    'name' => $breadcrumb['label']
                ]
            ];
        }

        return $items;
    }

    /**
     * @return AbstractBlock|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepareBreadcrumbs()
    {
        parent::prepareBreadcrumbs();

        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $this->addCrumb(
                $breadcrumbs,
                'post',
                [
                    'label' => $this->getPost()->getTitle(),
                    'title' => $this->getPost()->getTitle(),
                ]
            );
        }
    }

    /**
     * @return string
     */
    public function getSocialHtml()
    {
        return $this->getChildHtml('amblog_social');
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getHelpfulHtml()
    {
        $html = '';
        $block = $this->getChildBlock('amblog_helpful');
        if ($block) {
            $block->setPost($this->getPost());
            $html = $block->toHtml();
        }

        return $html;
    }

    /**
     * @return string
     */
    public function getColorClass()
    {
        return $this->getSettingHelper()->getIconColorClass();
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function hasThumbnailUrl()
    {
        $post = $this->getPost();
        if ($post) {
            return (bool)$post->getThumbnailUrl();
        }

        return false;
    }

    /**
     * @return mixed|string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getThumbnailUrl()
    {
        $url = '';
        $post = $this->getPost();
        if ($post) {
            $url = $post->getThumbnailUrl();
            $url = $this->filter->filter($url);
        }

        return $url;
    }

    /**
     * @return array|string[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getIdentities()
    {
        return [
            Posts::CACHE_TAG . '_' . $this->getPost()->getId(),
            Posts::POSITION_CACHE_TAG . '_' . $this->_storeManager->getStore()->getId()
        ];
    }

    /**
     * @return \Amasty\Blog\Model\Networks
     */
    public function getNetworksModel()
    {
        return $this->networksModel->create();
    }

    /**
     * @return bool
     */
    public function getUseCommentsGlobal()
    {
        return $this->getSettingHelper()->getUseComments();
    }

    public function isShowViewsCounter(): bool
    {
        return $this->getSettingHelper()->getDisplayViews();
    }
}
