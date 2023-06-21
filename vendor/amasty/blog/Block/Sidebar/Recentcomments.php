<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Sidebar;

use Amasty\Blog\Api\CommentRepositoryInterface;
use Amasty\Blog\Helper\Data;
use Amasty\Blog\Helper\Date;
use Amasty\Blog\Helper\Settings;
use Amasty\Blog\Model\ConfigProvider;
use Magento\Framework\View\Element\Template\Context;

class Recentcomments extends AbstractClass
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    private $collection;

    /**
     * @var CommentRepositoryInterface
     */
    private $commentRepository;

    public function __construct(
        Context $context,
        Settings $settingsHelper,
        Date $dateHelper,
        Data $dataHelper,
        CommentRepositoryInterface $commentRepository,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $settingsHelper, $dateHelper, $dataHelper, $configProvider, $data);
        $this->commentRepository = $commentRepository;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("Amasty_Blog::sidebar/recentcomments.phtml");
        $this->addAmpTemplate("Amasty_Blog::amp/sidebar/recentcomments.phtml");
        $this->setRoute('display_recent_comments');
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        $html = '';
        if ($this->getSettingsHelper()->getUseComments()) {
            $html = parent::toHtml();
        }

        return $html;
    }

    /**
     * Get header text
     *
     * @return string
     */
    public function getBlockHeader()
    {
        if (!$this->hasData('header_text')) {
            $this->setData('header_text', __('Recent comments'));
        }

        return $this->getData('header_text');
    }

    /**
     * @return \Amasty\Blog\Model\ResourceModel\Comments\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCommentsCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->commentRepository->getRecentComments();
            $this->collection->setPageSize($this->getCommentsLimit());
        }

        return $this->collection;
    }

    /**
     * Get show thesis
     *
     * @return bool
     */
    public function needShowThesis()
    {
        return (bool)$this->getSettingsHelper()->getRecentCommentsDisplayShort();
    }

    /**
     * Get show date
     *
     * @return bool
     */
    public function needShowDate()
    {
        if (!$this->hasData('display_date')) {
            $this->setData('display_date', $this->getSettingsHelper()->getRecentCommentsDisplayDate());
        }

        return (bool)$this->getData('display_date');
    }

    /**
     * @return string
     */
    public function getCommentsLimit()
    {
        if (!$this->hasData('comments_limit')) {
            $this->setData('comments_limit', $this->getSettingsHelper()->getCommentsLimit());
        }

        return $this->getData('comments_limit');
    }
}
