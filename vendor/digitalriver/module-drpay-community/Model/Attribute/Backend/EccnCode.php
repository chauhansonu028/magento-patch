<?php
/**
 * Saves EccnCode attribute information
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;

/**
 * Class EccnCode Backend
 */
class EccnCode extends AbstractBackend
{

    /**
     * Prepare data for save
     *
     * @param \Magento\Framework\DataObject $object
     * @return AbstractBackend
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $data = $object->getData($attributeCode);
        if ($data === '-- Select One --') {
            $object->setData($attributeCode, null);
        } else {
            $object->setData($attributeCode, $data);
        }

        return parent::beforeSave($object);
    }

    /**
     * After load method
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     */
    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $data = $object->getData($attributeCode);
        $object->setData($attributeCode, $data);

        return $this;
    }
}
