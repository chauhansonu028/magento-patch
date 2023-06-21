<?php
/**
 * Validate Digital River Allocated Percent Field: is summa equals 100
 *
 * @category   Digitalriver
 * @package    Digitalriver_DrPay
 */
namespace Digitalriver\DrPay\Model\BundleValidation;

use Digitalriver\DrPay\Api\ValidatorInterface;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 *  Class DigitalRiverEligible
 */
class DrAllocatedPercentSummaValidator implements ValidatorInterface
{
    /** @var int  */
    private const SUM_VALUE = 100;

    /** @var int  */
    private const PRICE_TYPE_DYNAMIC = 0;

    /**
     * @param ProductInterface $product
     * @return array|false
     */
    public function validate(ProductInterface $product)
    {
        if (!$product->getBundleOptionsData()
            || (int)$product->getPriceType() === self::PRICE_TYPE_DYNAMIC) {
            return false;
        }

        $summaryOfAllocatedPercent = 0;
        foreach ($product->getBundleOptionsData() as $option) {
            $summaryOfAllocatedPercent += (int)$option['dr_allocated_percent'];
        }
        
        if (self::SUM_VALUE != $summaryOfAllocatedPercent) {
            return [__('Digital River Allocated total value must be 100.')];
        }

        return false;
    }
}
