<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\ResourceModel\Traits;

use Amasty\Blog\Model\Blog\Registry;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;

trait ResourceModelTrait
{
    /**
     * @throws LocalizedException
     */
    public function addDefaultStoreSelect(DataObject $object, int $storeId = Store::DEFAULT_STORE_ID): void
    {
        $connection = $this->getConnection();
        $idFieldName = $this->getIdFieldName();
        $select = $connection->select()
            ->from($this->getTable(self::STORE_TABLE_NAME))
            ->where(sprintf('%s = :%s && store_id = %d', $idFieldName, $idFieldName, $storeId));
        $storesData = $connection->fetchRow($select, [':' . $idFieldName => $object->getId()]);

        if ($storesData) {
            $object->addData($storesData);
        }
    }

    /**
     * @param AbstractModel $object
     */
    private function saveStoreData($object)
    {
        if ($object->getData('saveStoreData') !== false) {
            $connection = $this->getConnection();

            $storeId = $object->getStoreId() ?: 0;
            $condition = [$this->getIdFieldName() . ' = ?' => $object->getId(), 'store_id = ?' => $storeId];
            $connection->delete($this->getTable(self::STORE_TABLE_NAME), $condition);

            $valuesForSave = $this->prepareStoreData($object);

            $connection->insert($this->getTable(self::STORE_TABLE_NAME), $valuesForSave);
        }
    }

    /**
     * @param AbstractModel $object
     * @return array
     */
    private function prepareStoreData($object)
    {
        $valuesForSave = [];
        foreach (self::STORE_TABLE_FIELDS as $value) {
            if ($value == 'store_id') {
                $valuesForSave[$value] = $object->getData($value) ?: 0;
            } else {
                if ($object->getData($value) && stripos($value, '_at') !== false) {
                    $valuesForSave[$value] = is_numeric($object->getData($value))
                        ? date('Y-m-d H:i:s', $object->getData($value))
                        : $object->getData($value);
                } else {
                    $valuesForSave[$value] = $object->getData($value);
                }
            }
        }

        return $valuesForSave;
    }
}
