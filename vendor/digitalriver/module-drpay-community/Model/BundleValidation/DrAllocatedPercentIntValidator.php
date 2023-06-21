<?php
/**
 * Validate Digital River Allocated Percent Field: is integer
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
class DrAllocatedPercentIntValidator implements ValidatorInterface
{

    /**
     * @param ProductInterface $product
     * @return array|false
     */
    public function validate(ProductInterface $product)
    {
        if (!$product->getBundleOptionsData()) {
            return false;
        }
        foreach ($product->getBundleOptionsData() as $option) {

            # will only be used to check if field is numeric
            $drAllocatedPercent = null;
            if (isset($option['dr_allocated_percent'])) {
                $drAllocatedPercent = $option['dr_allocated_percent'];
                if ('' === $drAllocatedPercent) {
                    # the field was not changed, we'll take the default value (0)
                    $drAllocatedPercent = 0;
                }
            }
            # ctype_digit returns false when 0
            if (!isset($option['dr_allocated_percent']) ||
                (0 !== $drAllocatedPercent && !ctype_digit($drAllocatedPercent))
            ) {
                return [__('Digital River Allocated value must be integer.')];
            }
        }

        return false;
    }
}
