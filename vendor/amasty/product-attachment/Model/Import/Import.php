<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Import;

use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;

class Import extends AbstractModel
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const IMPORT_ID = 'import_id';
    public const STORE_IDS = 'store_ids';
    public const CREATED_AT = 'created_at';
    public const IMPORT_FILE = 'import_file';
    /**#@-*/

    public const IMPORT_FILE_ID = 'import_file_id';

    public function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\ProductAttachment\Model\Import\ResourceModel\Import::class);
        $this->setIdFieldName(self::IMPORT_ID);
    }

    /**
     * @param int $importId
     *
     * @return Import
     */
    public function setImportId($importId)
    {
        return $this->setData(self::IMPORT_ID, (int)$importId);
    }

    /**
     * @return int
     */
    public function getImportId()
    {
        return (int)$this->_getData(self::IMPORT_ID);
    }

    /**
     * @param string|array $storeIds
     *
     * @return Import
     */
    public function setStoreIds($storeIds)
    {
        if (is_array($storeIds)) {
            return $this->setData(self::STORE_IDS, implode(',', $storeIds));
        }

        return $this->setData(self::STORE_IDS, $storeIds);
    }

    /**
     * @return array
     */
    public function getStoreIds()
    {
        $storeIds = $this->_getData(self::STORE_IDS) ?? [Store::DEFAULT_STORE_ID];
        if (!is_array($storeIds)) {
            $storeIds = explode(',', $storeIds);
        }

        return $storeIds;
    }

    /**
     * @return int
     */
    public function getCreatedAt()
    {
        return $this->_getData(self::CREATED_AT);
    }

    public function setImportFile(string $fileData): Import
    {
        return $this->setData(self::IMPORT_FILE, $fileData);
    }

    public function getImportFile(): string
    {
        return (string)$this->_getData(self::IMPORT_FILE);
    }
}
