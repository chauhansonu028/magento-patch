<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Content;

use Amasty\Blog\Api\Data\TagInterface;
use Amasty\Blog\Model\Blog\Registry;

class Tag extends Lists implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var \Amasty\Blog\Model\Tag
     */
    private $tag;

    protected function _construct()
    {
        $this->isTag = true;
        parent::_construct();
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getToolbar()->setPagerObject($this->getTag());

        return $this;
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
            $breadcrumbs->addCrumb(
                $this->getTag()->getUrlKey(),
                [
                    'label' => $this->getTag()->getName(),
                    'title' => $this->getTag()->getName(),
                ]
            );
        }
    }

    private function getTag(): ?TagInterface
    {
        if (!$this->tag) {
            $this->tag = $this->getRegistry()->registry(Registry::CURRENT_TAG);
            if (!$this->tag) {
                //deprecated remove unnecessary code in next releases
                try {
                    $this->tag = $this->getTagRepository()->getByIdAndStore(
                        (int)$this->getRequest()->getParam('id'),
                        (int)$this->_storeManager->getStore()->getId()
                    );

                } catch (\Exception $e) {
                    $this->_logger->critical($e->getMessage());
                    return $this->getTagRepository()->getTagModel();
                }
            }
        }

        return $this->tag;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\Amasty\Blog\Model\Tag::CACHE_TAG . '_' . $this->getTag()->getId()];
    }
}
