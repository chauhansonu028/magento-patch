<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Content\Post;

use Amasty\Blog\Api\CategoryRepositoryInterface;
use Amasty\Blog\Api\CommentRepositoryInterface;
use Amasty\Blog\Api\TagRepositoryInterface;
use Amasty\Blog\Helper\Date;
use Amasty\Blog\Model\Blog\Registry;
use Amasty\Blog\Model\ConfigProvider;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Details extends Template
{
    /**
     * @var Date
     */
    private $helperDate;

    /**
     * @var CommentRepositoryInterface
     */
    private $commentRepository;

    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var \Amasty\Blog\Model\Posts
     */
    private $post;

    /**
     * @var ObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Context $context,
        Date $helperDate,
        TagRepositoryInterface $tagRepository,
        CategoryRepositoryInterface $categoryRepository,
        CommentRepositoryInterface $commentRepository,
        ConfigProvider $configProvider,
        ObjectFactory $dataObjectFactory,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helperDate = $helperDate;
        $this->commentRepository = $commentRepository;
        $this->tagRepository = $tagRepository;
        $this->categoryRepository = $categoryRepository;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->configProvider = $configProvider;
        $this->registry = $registry;
    }

    /**
     * @param $post
     *
     * @return $this
     */
    public function setPost($post)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * @return \Amasty\Blog\Model\Posts
     */
    public function getPost()
    {
        if ($this->post === null) {
            $this->post = $this->registry->registry(Registry::CURRENT_POST);
        }

        return $this->post;
    }

    public function renderDate(string $datetime, bool $isEditedAt = false): string
    {
        return $this->helperDate->renderDate($datetime, false, false, $isEditedAt);
    }

    /**
     * @return string
     */
    public function getCommentsUrl()
    {
        return $this->getPost()->getUrl() . "#comments";
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCommentsCount()
    {
        $commentsCollection = $this->commentRepository->getCommentsInPost($this->getPost()->getId())->addActiveFilter();

        return $commentsCollection->getSize();
    }

    /**
     * @param bool $isAmp
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTagsHtml($isAmp = false)
    {
        $template = $isAmp ? 'Amasty_Blog::amp/list/tags.phtml' : 'Amasty_Blog::list/tags.phtml';

        return $this->getHtml(\Amasty\Blog\Block\Content\Post\Details::class, $template);
    }

    /**
     * @param bool $isAmp
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoriesHtml($isAmp = false)
    {
        $template = $isAmp ? 'Amasty_Blog::amp/list/categories.phtml' : 'Amasty_Blog::list/categories.phtml';

        return $this->getHtml(\Amasty\Blog\Block\Content\Post\Details::class, $template);
    }

    /**
     * @param $blockClass
     * @param $template
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getHtml($blockClass, $template)
    {
        $block = $this->getLayout()->createBlock($blockClass);
        $html = '';
        if ($block) {
            $block->setPost($this->getPost())->setTemplate($template);
            $html = $block->toHtml();
        }

        return $html;
    }

    /**
     * @return array|\Magento\Framework\DataObject[]
     */
    public function getTags()
    {
        if ($this->getPost()->isPreviewPost()) {
            $result = [];
            $tags = $this->getPost()->getData('tags');
            $tagsArray = explode(',', $tags);
            foreach ($tagsArray as $tag) {
                if ($tag) {
                    $result[] = $this->dataObjectFactory->create(
                        [
                            'data' => [
                                'name' => $tag
                            ]
                        ]
                    );
                }
            }

            return $result;
        }

        $tagsIds = $this->getPost()->getTagIds();
        $tagsIds = is_array($tagsIds) ? $tagsIds : explode(',', $tagsIds);

        return $this->tagRepository->getTagsByIds($tagsIds)->getItems();
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategories()
    {
        $categories = $this->getPost()->getCategories();

        if (!is_array($categories)) {
            $categories = $categories ? explode(',', $categories) : [];
        }

        $collection = $this->categoryRepository->getCategoriesByIds($categories);
        $limit = $this->configProvider->getCategoryLimitOnPost();
        if ($limit) {
            $collection->setPageSize($limit);
        }

        return $collection;
    }

    public function isShowAuthorInfo(): bool
    {
        return $this->configProvider->isShowAuthorInfo();
    }

    public function getColorClass(): string
    {
        return $this->configProvider->getIconColorClass();
    }

    public function isHumanized(): bool
    {
        return $this->configProvider->getDateFormat() === Date::DATE_TIME_PASSED;
    }

    public function isShowEditedAt(): bool
    {
        return $this->configProvider->isShowEditedAt();
    }
}
