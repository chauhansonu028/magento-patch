<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Import\ResourceModel;

use Amasty\ProductAttachment\Model\Import\Import as ImportModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Import extends AbstractDb
{
    public const TABLE_NAME = 'amasty_file_import';

    public function _construct()
    {
        $this->_init(self::TABLE_NAME, ImportModel::IMPORT_ID);
    }
}
