<?php
/**
 * Validate Digital River bundle product, make sure, that it satisfies all business requirements
 *
 * @category   Digitalriver
 * @package    Digitalriver_DrPay
 */

declare(strict_types=1);

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Api\ValidatorInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Exception\ConfigurationMismatchException;

/**
 *  Class DigitalRiverEligible
 */
class BundleValidation implements ValidatorInterface
{

    /**
     * @var array
     */
    private $validators = [];

    /**
     * BundleValidation constructor.
     * @param array $validators
     * @throws ConfigurationMismatchException
     */
    public function __construct(array $validators = [])
    {
        foreach ($validators as $validator) {
            if (!$validator instanceof ValidatorInterface) {
                throw new ConfigurationMismatchException(
                    __('The "%1" validator is not an instance of the general validator interface.', $validator)
                );
            }
        }
        $this->validators = $validators;
    }

    /**
     * @param ProductInterface $product
     * @return array|false|\Magento\Framework\Phrase[]
     * @throws ConfigurationMismatchException
     */
    public function validate(ProductInterface $product)
    {
        $messages = [];
        if ($product->getTypeId() == Type::TYPE_BUNDLE) {
            foreach ($this->validators as $validator) {
                $validatorMessages = $validator->validate($product);
                if ($validatorMessages) {
                    $messages = array_merge($messages, $validatorMessages);
                }
            }
        }

        return $messages;
    }
}
