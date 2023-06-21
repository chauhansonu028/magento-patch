<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Content;

use Amasty\Blog\Api\AuthorRepositoryInterface;
use Amasty\Blog\Api\CategoryRepositoryInterface;
use Amasty\Blog\Api\PostRepositoryInterface;
use Amasty\Blog\Api\TagRepositoryInterface;
use Amasty\Blog\Helper\Data;
use Amasty\Blog\Helper\Date;
use Amasty\Blog\Helper\Settings;
use Amasty\Blog\Helper\Url;
use Amasty\Blog\Model\Blog\Registry;
use Amasty\Blog\Model\ConfigProvider;
use Amasty\Blog\Model\Lists as ListsModel;
use Amasty\Blog\Model\ListsFactory;
use Amasty\Blog\Model\Posts;
use Amasty\Blog\Model\UrlResolver;
use Amasty\Blog\ViewModel\Author\SmallImage;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template\Context;

class Lists extends AbstractBlock implements IdentityInterface
{
    public const PAGER_BLOCK_NAME = 'amblog_list_pager';

    /**
     * @var $collection
     */
    protected $collection;

    /**
     * @var bool
     */
    protected $isCategory = false;

    /**
     * @var bool
     */
    protected $isTag = false;

    /**
     * @var null
     */
    private $toolbar = null;

    /**
     * @var ListsFactory
     */
    private $listsModel;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var AuthorRepositoryInterface
     */
    private $authorRepository;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Context $context,
        Data $dataHelper,
        Settings $settingsHelper,
        Url $urlHelper,
        TagRepositoryInterface $tagRepository,
        AuthorRepositoryInterface $authorRepository,
        CategoryRepositoryInterface $categoryRepository,
        PostRepositoryInterface $postRepository,
        ListsFactory $listsModel,
        Date $helperDate,
        UrlResolver $urlResolver,
        Registry $registry,
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
        $this->listsModel = $listsModel;
        $this->postRepository = $postRepository;
        $this->tagRepository = $tagRepository;
        $this->authorRepository = $authorRepository;
        $this->categoryRepository = $categoryRepository;
        $this->registry = $registry;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getToolbar()
            ->setPagerObject($this->listsModel->create())
            ->setLimit($this->getSettingHelper()->getPostsLimit())
            ->setCollection($this->getCollection());

        return $this;
    }

    /**
     * @param $post
     * @return string
     */
    public function getReadMoreUrl($post)
    {
        return $this->getUrlResolverModel()->getPostUrlByIdAndStore((int)$post->getId());
    }

    /**
     * @param \Amasty\Blog\Model\ResourceModel\Posts\Collection $collection
     * @return $this
     */
    private function checkTag($collection)
    {
        if (($id = $this->getRequest()->getParam('id')) && $this->isTag) {
            $collection->addTagFilter($id);
        }

        return $this;
    }

    /**
     * @param \Amasty\Blog\Model\ResourceModel\Posts\Collection $collection
     * @return $this
     */
    private function checkCategory($collection)
    {
        if (($id = $this->getRequest()->getParam('id')) && $this->isCategory) {
            $collection->addCategoryFilter($id);
        }

        return $this;
    }

    /**
     * @return \Amasty\Blog\Model\ResourceModel\Posts\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCollection()
    {
        if (!$this->collection) {
            $posts = $this->postRepository->getActivePosts();

            $posts->setUrlKeyIsNotNull();
            $posts->setDateOrder();
            $posts->setPostIdOrder();

            $this->checkCategory($posts);
            $this->checkTag($posts);

            $this->collection = $posts;
        }

        return $this->collection;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getToolbar()
    {
        if (!$this->toolbar) {
            $toolbar = $this->getLayout()->createBlock(\Amasty\Blog\Block\Content\Lists\Pager::class);
            $this->getLayout()->setBlock(self::PAGER_BLOCK_NAME, $toolbar);
            $this->toolbar = $toolbar;
        }

        return $this->toolbar;
    }

    /**
     * @param bool $isAmp
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getToolbarHtml($isAmp = false)
    {
        $template = $isAmp ? 'Amasty_Blog::amp/list/pager.phtml' : 'Amasty_Blog::list/pager.phtml';

        return $this->getToolbar()->setTemplate($template)->toHtml();
    }

    /**
     * @return string
     */
    public function getColorClass()
    {
        return $this->getSettingHelper()->getIconColorClass();
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $cacheTags = [ListsModel::CACHE_TAG];
        $posts = $this->getCollection()->getItems();

        return array_reduce($posts, function (array $carry, Posts $post): array {
            return array_merge($carry, $post->getIdentities());
        }, $cacheTags);
    }

    /**
     * @return TagRepositoryInterface
     */
    public function getTagRepository()
    {
        return $this->tagRepository;
    }

    /**
     * @return AuthorRepositoryInterface
     */
    public function getAuthorRepository()
    {
        return $this->authorRepository;
    }

    /**
     * @return CategoryRepositoryInterface
     */
    public function getCategoryRepository()
    {
        return $this->categoryRepository;
    }

    /**
     * @return PostRepositoryInterface|\Amasty\Blog\Model\ResourceModel\Posts\Collection
     */
    public function getPostRepository()
    {
        return $this->postRepository;
    }

    /**
     * @return Registry
     */
    public function getRegistry(): Registry
    {
        return $this->registry;
    }

    public function isCanRender(): bool
    {
        $collection = $this->getCollection();

        return null !== $collection && $collection->count() > 0;
    }
}
