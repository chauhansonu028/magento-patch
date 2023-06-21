<?php
namespace Digitalriver\DrPay\Api;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Interface ValidatorInterface
 */
interface ValidatorInterface
{
    /**
     * @param ProductInterface $product
     * @return \Magento\Framework\Phrase[]|false
     */
    public function validate(ProductInterface $product);
}
