<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Content;

use Amasty\Blog\Api\Data\AuthorInterface;

class Author extends Lists implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var AuthorInterface
     */
    private $author;

    /**
     * @return $this|Lists
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getToolbar()->setPagerObject($this->getAuthor());

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
                $this->getAuthorUrlKey(),
                [
                    'label' => $this->getAuthor()->getName(),
                    'title' => $this->getAuthor()->getName(),
                ]
            );
        }
    }

    /**
     * @return \Amasty\Blog\Model\ResourceModel\Posts\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCollection()
    {
        if (!$this->collection) {
            parent::getCollection();
            $this->collection->addFieldToFilter(
                AuthorInterface::AUTHOR_ID,
                ['eq' => $this->getAuthor()->getAuthorId()]
            );
        }

        return $this->collection;
    }

    /**
     * @return string
     */
    public function getAuthorName()
    {
        return $this->getAuthor()->getName();
    }

    public function getDescription(): ?string
    {
        return $this->getAuthor()->getDescription();
    }

    /**
     * @return string
     */
    private function getAuthorUrlKey()
    {
        return $this->getAuthor()->getUrlKey();
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\Amasty\Blog\Model\Author::CACHE_TAG . '_' . $this->getAuthorUrlKey()];
    }

    /**
     * @return AuthorInterface
     */
    private function getAuthor()
    {
        try {
            if (!$this->author) {
                //TODO get from registry
                $this->author = $this->getAuthorRepository()->getByIdAndStore(
                    (int)$this->getRequest()->getParam('id'),
                    (int)$this->_storeManager->getStore()->getId()
                );
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);

            $this->author = $this->getAuthorRepository()->getAuthorModel();
        }

        return $this->author;
    }
}
