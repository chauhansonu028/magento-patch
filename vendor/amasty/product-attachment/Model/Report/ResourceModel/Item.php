<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Report\ResourceModel;

use Amasty\ProductAttachment\Model\File\ResourceModel\File;
use Amasty\ProductAttachment\Model\Report\Item as ItemModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Item extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(File::REPORT_TABLE_NAME, ItemModel::ITEM_ID);
    }

    public function clear()
    {
        $this->getConnection()->truncateTable($this->getMainTable());
    }
}
