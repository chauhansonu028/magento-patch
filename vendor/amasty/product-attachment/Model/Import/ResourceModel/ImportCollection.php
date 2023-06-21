<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Import\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class ImportCollection extends AbstractCollection
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
            \Amasty\ProductAttachment\Model\Import\Import::class,
            \Amasty\ProductAttachment\Model\Import\ResourceModel\Import::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
