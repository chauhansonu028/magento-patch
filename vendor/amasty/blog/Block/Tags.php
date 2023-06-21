<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block;

use Amasty\Blog\Api\TagRepositoryInterface;
use Amasty\Blog\Helper\Settings;
use Amasty\Blog\Model\ResourceModel\Tag\Collection;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Tags extends Template
{
    /**
     * @var AbstractCollection
     */
    private $collection;

    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * @var Settings
     */
    private $settingsHelper;

    /**
     * @var int
     */
    private $postsCount = 0;

    public function __construct(
        Context $context,
        TagRepositoryInterface $tagRepository,
        Settings $settingsHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->tagRepository = $tagRepository;
        $this->settingsHelper = $settingsHelper;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("Amasty_Blog::tags.phtml");
        $this->setRoute('use_tags');
    }

    /**
     * @param int $storeId
     * @return Collection
     */
    public function getCollection($storeId = null)
    {
        if (!$this->collection) {
            $collection = $this->tagRepository->getActiveTags($storeId);
            $collection->setMinimalPostCountFilter($this->settingsHelper->getTagsMinimalPostCount());
            $tagLimit = $this->settingsHelper->getTagLimit();
            if (!empty($tagLimit) || $tagLimit != 0) {
                $collection->setLimit((int)$tagLimit);
                $collection->setOrderById();
            } else {
                $collection->setNameOrder();
            }
            $this->collection = $collection;
        }

        return $this->collection;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPostsCount()
    {
        if (!$this->postsCount) {
            foreach ($this->getCollection() as $item) {
                $this->postsCount += $item->getPostCount();
            }
        }

        return $this->postsCount;
    }

    /**
     * @param $postsInTagCount
     * @return float|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTagWeight($postsInTagCount)
    {
        $postsCount = $this->getPostsCount();

        return $postsCount ? (($postsInTagCount * 100) / $postsCount) : 0;
    }

    /**
     * @param \Amasty\Blog\Model\Tag $tag
     * @return bool
     */
    public function isActive(\Amasty\Blog\Model\Tag $tag)
    {
        $result = false;
        if ($this->getRequest()->getModuleName() == "amblog"
            && $this->getRequest()->getControllerName() == "index"
            && $this->getRequest()->getActionName() == "tag"
            && $this->getRequest()->getParam('id') == $tag->getTagId()
        ) {
            $result = true;
        }

        return $result;
    }
}
