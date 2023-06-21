<?php

namespace Digitalriver\DrPay\Model;

class PrimarySourceValidatorPool
{
    /**
     * @var PrimarySourceValidatorInterface[]
     */
    private $validators;

    /**
     * @param PrimarySourceValidatorInterface[] $validators
     * @throws \Magento\Framework\Exception\InvalidArgumentException
     */
    public function __construct(array $validators = [])
    {
        foreach ($validators as $validator) {
            if (!($validator instanceof PrimarySourceValidatorInterface)) {
                throw new \Magento\Framework\Exception\InvalidArgumentException(
                    __('Provided PrimarySourceValidator does not have the correct type.')
                );
            }
        }

        $this->validators = $validators;
    }

    /**
     * @param string $primarySourceId
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @return array Errors returned.
     */
    public function execute(string $primarySourceId, \Magento\Quote\Api\Data\CartInterface $cart): array
    {
        $errors = [];

        foreach ($this->validators as $validator) {
            try {
                $validator->validatePrimarySource($primarySourceId, $cart);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $errors[] = $e->getMessage();
            }
        }

        return $errors;
    }
}
