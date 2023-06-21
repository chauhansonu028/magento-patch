<?php
/**
 * Validate Digital River Allocated Percent Field: Eccn code
 *
 * @category   Digitalriver
 * @package    Digitalriver_DrPay
 */
namespace Digitalriver\DrPay\Model\BundleValidation;

use Digitalriver\DrPay\Api\ValidatorInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;

class DigitalRiverEligible implements ValidatorInterface
{
    const DR_ECCN_CODE = 'dr_eccn_code';

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param ProductRepository $productRepository
     */
    public function __construct(
        ProductRepository $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * @param ProductInterface $product
     * @return array|false
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function validate(ProductInterface $product)
    {
        if (!$product->getBundleOptionsData()) {
            return false;
        }
        foreach ($product->getBundleOptionsData() as $option) {
            if (!isset($option["bundle_button_proxy"][0]['entity_id'])) {
                return [__('Cannot get child product')];
            }

            $childProduct = $this->productRepository->getById($option["bundle_button_proxy"][0]['entity_id']);
            if (!$childProduct->getData(self::DR_ECCN_CODE)) {
                return [__('ECCN Code must be defined for each product')];
            }
        }

        return false;
    }
}
