<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Icon\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\ProductAttachment\Model\Icon\Icon::class,
            \Amasty\ProductAttachment\Model\Icon\ResourceModel\Icon::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
