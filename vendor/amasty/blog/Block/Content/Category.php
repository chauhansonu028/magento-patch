<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Content;

use Amasty\Blog\Api\Data\CategoryInterface;
use Amasty\Blog\Model\Blog\Registry;
use Magento\Framework\DataObject\IdentityInterface;

class Category extends \Amasty\Blog\Block\Content\Lists implements IdentityInterface
{
    /**
     * @var $category
     */
    private $category;

    protected function _construct()
    {
        $this->isCategory = true;
        parent::_construct();
    }

    /**
     * @return $this|Lists
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getToolbar()->setPagerObject($this->getCategory());

        return $this;
    }

    /**
     * @return Lists|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepareBreadcrumbs()
    {
        parent::prepareBreadcrumbs();
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                $this->getCategory()->getUrlKey(),
                [
                    'label' => $this->getCategory()->getName(),
                    'title' => $this->getCategory()->getName(),
                ]
            );
        }
    }

    /**
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->getCategory()->getMetaTitle()
            ? $this->getSettingHelper()->getPrefixTitle($this->getCategory()->getMetaTitle())
            : $this->getSettingHelper()->getPrefixTitle($this->getCategory()->getName());
    }

    public function getCategory(): ?CategoryInterface
    {
        if (!$this->category) {
            $this->category = $this->getRegistry()->registry(Registry::CURRENT_CATEGORY);
            if (!$this->category) {
                //deprecated remove unnecessary code in next releases
                try {
                    $this->category = $this->getCategoryRepository()->getByIdAndStore(
                        (int)$this->getRequest()->getParam('id'),
                        (int)$this->_storeManager->getStore()->getId()
                    );

                } catch (\Exception $e) {
                    $this->_logger->critical($e->getMessage());
                    return $this->getCategoryRepository()->getCategory();
                }
            }
        }

        return $this->category;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\Amasty\Blog\Model\Categories::CACHE_TAG . '_' . $this->getCategory()->getId()];
    }
}
