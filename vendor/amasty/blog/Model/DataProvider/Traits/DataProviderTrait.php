<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\DataProvider\Traits;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

trait DataProviderTrait
{
    /**
     * @param object $current
     * @param int $storeId
     * @param array $data
     * @return array
     */
    public function prepareData($current, $storeId, $data)
    {
        if ($current && $current->getId()) {
            $data[$current->getId()] = $current->getData();
            $this->addDataByStore($data, $storeId, $current->getId());
        }

        return $data;
    }

    public function addDataByStore(array &$data, int $storeId, int $currentEntityId): void
    {
        $data[$currentEntityId]['store_id'] = $storeId;
        if ($item = $this->repository->getByIdAndStore($currentEntityId, $storeId)) {
            foreach ($this->getFieldsByStore() as $fieldSet) {
                $data = $this->addDataToFieldSet($fieldSet, $item, $data, $currentEntityId);
            }
        }
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }

    private function addDataToFieldSet(array $fieldSet, DataObject $item, array $data, int $currentEntityId): array
    {
        foreach ($fieldSet as $field) {
            if (is_array($field)) {
                foreach ($field as $value) {
                    if ($item->getData($value) !== null) {
                        $data[$currentEntityId][$value] = $item->getData($value);
                    }
                }
            } else {
                if ($item->getData($field) !== null) {
                    $data[$currentEntityId][$field] = $item->getData($field);
                }
            }
        }

        return $data;
    }
}
