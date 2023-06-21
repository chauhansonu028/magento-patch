<?php
/**
 * HS code validator.
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\Attribute\Backend;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class HsCode Backend
 */
class HsCode extends AbstractBackend
{
    /**
     * Validate HS Code attribute
     *
     * @param Product $object
     * @return bool
     * @throws LocalizedException
     */
    public function validate($object): bool
    {
        $value = $object->getData($this->getAttribute()->getAttributeCode());

        if ($value == "") {
            return true;
        }

        $value = ltrim($value);
        $value = rtrim($value);

        if (preg_match('/^[0-9]\d{3}\.\d{2}(\.\d{1,4})?$/', $value)) {
            return true;
        } else {
            throw new LocalizedException(
                __('Invalid HS code Format')
            );
        }
    }

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

        if ($data != "") {
            $data = ltrim($data);
            $data = rtrim($data);
        }
        $object->setData($attributeCode, $data);

        return parent::beforeSave($object);
    }
}
