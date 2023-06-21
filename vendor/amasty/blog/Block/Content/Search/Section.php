<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Content\Search;

use Amasty\Blog\Block\Content\Lists\Pager;
use Amasty\Blog\Model\ResourceModel\Posts\Collection;
use Amasty\Blog\Model\ResourceModel\Posts\CollectionFactory as PostCollectionFactory;
use Magento\Backend\Block\Template;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Section extends Template
{
    public function getCollection(): ?AbstractCollection
    {
        return $this->getData('collection');
    }

    /**
     * @throws LocalizedException
     */
    public function getToolbar(bool $isAmp = false): Pager
    {
        /** @var Pager $toolbar */
        $toolbar = $this->getLayout()->createBlock(Pager::class);
        $template = $isAmp ? 'Amasty_Blog::amp/list/pager.phtml' : 'Amasty_Blog::list/pager.phtml';
        $toolbar
            ->setTemplate($template)
            ->setPageVarName($this->getData('entityName') . '_page')
            ->setLimitVarName($this->getData('entityName') . '_limit')
            ->setCollection($this->getCollection())
            ->setIsMultiSearch(true)
            ->setSearchPage(true);

        return $toolbar;
    }
}
