<?php
/**
 * Validate Digital River Allocated Percent Field: product type & pryce type & total value
 *
 * @category   Digitalriver
 * @package    Digitalriver_DrPay
 */
namespace Digitalriver\DrPay\Model\BundleValidation;

use Digitalriver\DrPay\Api\ValidatorInterface;
use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Api\Data\ProductInterface;

class DynamicPriceValidator implements ValidatorInterface
{
    /**
     * @param ProductInterface $product
     * @return \Magento\Framework\Phrase[]|false
     */
    public function validate(ProductInterface $product)
    {
        if (!$product->getBundleOptionsData()) {
            return false;
        }

        /** checking for compliance with product price type */
        if (Price::PRICE_TYPE_DYNAMIC == $product->getPriceType() && BundleType::TYPE_CODE !== $product->getTypeId()) {
            return [__('Digital River Allocated entity is available only for a bundle products with fixed price')];
        }

        return false;
    }
}
