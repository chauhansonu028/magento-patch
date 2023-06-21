<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Sidebar;

use Amasty\Blog\Api\CategoryRepositoryInterface;
use Amasty\Blog\Api\PostRepositoryInterface;
use Amasty\Blog\Helper\Data;
use Amasty\Blog\Helper\Date;
use Amasty\Blog\Helper\Settings;
use Amasty\Blog\Model\ConfigProvider;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\Store;
use Magento\Widget\Model\Widget\InstanceFactory;

class Recentpost extends AbstractClass
{
    /**
     * @var $collection
     */
    private $collection;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var \Magento\Widget\Model\ResourceModel\Widget\Instance\CollectionFactory
     */
    private $widgetFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Context $context,
        Settings $settingsHelper,
        Date $dateHelper,
        Data $dataHelper,
        CategoryRepositoryInterface $categoryRepository,
        PostRepositoryInterface $postRepository,
        InstanceFactory $widgetFactory,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $settingsHelper, $dateHelper, $dataHelper, $configProvider, $data);
        $this->categoryRepository = $categoryRepository;
        $this->postRepository = $postRepository;
        $this->widgetFactory = $widgetFactory;
        $this->storeManager = $context->getStoreManager();
        $this->configProvider = $configProvider;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("Amasty_Blog::sidebar/recentpost.phtml");
        $this->addAmpTemplate('Amasty_Blog::amp/sidebar/recentpost.phtml');
        $this->setRoute('display_recent');
    }

    /**
     * Get header text
     *
     * @return string
     */
    public function getBlockHeader()
    {
        if (!$this->hasData('header_text')) {
            $this->setData('header_text', __('Recent posts'));
        }

        return $this->getData('header_text');
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function toHtml()
    {
        if ($this->getRequest()->getActionName() == 'preview') {
            return parent::toHtml();
        }

        $result = '';
        $widget = $this->widgetFactory->create()->load($this->getInstanceId());

        if ($widget->getInstanceId()) {
            $storeIds = $widget->getStoreIds();

            if (!is_array($storeIds)) {
                $storeIds = [Store::DEFAULT_STORE_ID];
            }

            $currentStore = $this->storeManager->getStore()->getId();

            if (in_array($currentStore, $storeIds) || in_array(Store::DEFAULT_STORE_ID, $storeIds)) {
                $result = parent::toHtml();
            }
        } else {
            $result = parent::toHtml();
        }

        return $result;
    }

    /**
     * Get show images
     *
     * @return bool
     */
    public function showImages()
    {
        if (!$this->hasData('show_images')) {
            $this->setData('show_images', $this->getSettingsHelper()->isRecentPostsUseImage());
        }

        return (bool)$this->getData('show_images');
    }

    /**
     * @return \Amasty\Blog\Model\ResourceModel\Posts\Collection
     */
    public function getCollection()
    {
        if (!$this->collection) {
            $collection = $this->postRepository->getRecentPosts();
            $collection->setPageSize($this->getPostsLimit());
            $this->checkCategory($collection);
            $this->preparePostCollection($collection);
            $this->collection = $collection;
        }

        return $this->collection;
    }

    /**
     * @return int
     */
    private function getPostsLimit()
    {
        return $this->getData('posts_limit') ?: (int)$this->getSettingsHelper()->getRecentPostsLimit();
    }

    /**
     * Get show thesis
     *
     * @return bool
     */
    public function needShowThesis()
    {
        if (!$this->hasData('display_short')) {
            $this->setData('display_short', $this->getSettingsHelper()->getRecentPostsDisplayShort());
        }

        return (bool)$this->getData('display_short');
    }

    /**
     * Get show date
     *
     * @return bool
     */
    public function needShowDate()
    {
        if (!$this->hasData('display_date')) {
            $this->setData('display_date', $this->getSettingsHelper()->getRecentPostsDisplayDate());
        }

        return (bool)$this->getData('display_date');
    }

    /**
     * @param $post
     *
     * @return bool
     */
    public function hasThumbnail($post)
    {
        return $post->getPostThumbnail() || $post->getListThumbnail();
    }

    /**
     * @param $post
     * @return bool|string
     */
    public function getThumbnailSrc($post)
    {
        return $post->getPostSidebarSrc();
    }

    /**
     * @return CategoryRepositoryInterface
     */
    public function getCategoryRepository()
    {
        return $this->categoryRepository;
    }

    /**
     * @return int
     */
    protected function getShortContentLimit()
    {
        return $this->getShortLimit() ?: parent::getShortContentLimit();
    }

    public function isHumanized(): bool
    {
        if (!$this->getData('date_manner')) {
            return parent::isHumanized();
        }

        return $this->getData('date_manner') === Date::DATE_TIME_PASSED;
    }
}
