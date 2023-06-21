<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Content\Post;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Api\PostRepositoryInterface;
use Amasty\Blog\Helper\Date;
use Amasty\Blog\Model\Blog\Registry;
use Amasty\Blog\Model\ConfigProvider;
use Amasty\Blog\Model\UrlResolver;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Related extends Template
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var PostInterface
     */
    private $post;

    /**
     * @var \Amasty\Blog\Model\ResourceModel\Posts\Collection
     */
    protected $collection = null;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var Date
     */
    private $helperDate;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Context $context,
        Registry $registry,
        PostRepositoryInterface $postRepository,
        Date $helperDate,
        UrlResolver $urlResolver,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->postRepository = $postRepository;
        $this->helperDate = $helperDate;
        $this->urlResolver = $urlResolver;
        $this->configProvider = $configProvider;
    }

    /**
     * @return \Amasty\Blog\Model\ResourceModel\Posts\Collection
     */
    public function getCollection()
    {
        if (!$this->collection && $this->getPost() && $this->getPost()->getRelatedPostIds()) {
            $postIds = explode(',', $this->getPost()->getRelatedPostIds());
            $this->collection = $this->postRepository->getActivePosts()
                ->addFieldToFilter(PostInterface::POST_ID, ['in' => $postIds])
                ->setUrlKeyIsNotNull()
                ->setDateOrder();
        }

        return $this->collection;
    }

    /**
     * @return PostInterface
     */
    public function getPost()
    {
        if ($this->post === null) {
            $this->post = $this->registry->registry(Registry::CURRENT_POST);
        }

        return $this->post;
    }

    /**
     * @param $post
     * @return string
     */
    public function getReadMoreUrl($post)
    {
        return $this->urlResolver->getPostUrlByIdAndStore((int)$post->getId());
    }

    public function renderDate(string $datetime, bool $isEditedAt = false): string
    {
        return $this->helperDate->renderDate($datetime, false, false, $isEditedAt);
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
}
