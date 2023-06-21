<?php
/**
 * Validates HS code attribute during product import
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Plugin\CatalogImportExport\Import\Product;

use Digitalriver\DrPay\Model\ResourceModel\TaxTypes\CollectionFactory;
use Magento\CatalogImportExport\Model\Import\Product\Validator;

/**
 * Class Validator
 */
class ValidatorPlugin extends Validator
{
    /**
     * @var CollectionFactory
     */
    private $taxTypes;

    /**
     * ValidatorPlugin constructor.
     * @param CollectionFactory $taxTypes
     */
    public function __construct(
        CollectionFactory $taxTypes
    ) {
        $this->taxTypes = $taxTypes;
    }

    /**
     * Checks if HS code attribute is valid
     *
     * @param Validator $validator
     * @param $result
     * @param $attrCode
     * @param array $attrParams
     * @param array $rowData
     * @return bool
     */
    public function afterIsAttributeValid(
        Validator $validator,
        $result,
        $attrCode,
        array $attrParams,
        array $rowData
    ): bool {
        if ($attrCode === 'dr_tax_type') {
            $taxGroupValue = $rowData['dr_tax_group'];
            $dataCollection = $this->taxTypes->create()
                ->addFieldToFilter('dr_tax_type', ['eq' => $rowData[$attrCode]])
                ->getData();

            foreach ($dataCollection as $item) {
                if ($item['dr_tax_group'] === $taxGroupValue) {
                    return $result;
                }
            }
            $validator->_addMessages(["Value for 'dr_tax_type' attribute contains incorrect value"]);
            return false;
        }

        if ($attrCode === 'dr_hs_code') {
            $valid = $this->validateHsCode($attrCode, $rowData[$attrCode], $validator);
            if ($valid) {
                return $result;
            } else {
                $result = false;
                return $result;
            }
        } else {
            return $result;
        }
    }

    /**
     * Validates the HS code
     *
     * @param $attrCode
     * @param $value
     * @param $validator
     * @return bool
     */
    protected function validateHsCode($attrCode, $value, $validator): bool
    {
        if ($value == "") {
            return true;
        }
        $value = ltrim($value);
        $value = rtrim($value);
        if (preg_match('/^[0-9]\d{3}\.\d{2}(\.\d{1,4})?$/', $value)) {
            $valid = true;
        } else {
            $valid = false;
        }
        if (!$valid) {
            $validator->_addMessages(["Value for 'dr_hs_code' attribute contains incorrect value"]);
        }
        return $valid;
    }
}
