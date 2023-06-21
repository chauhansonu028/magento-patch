<?php

namespace Digitalriver\DrPay\Model;

interface PrimarySourceValidatorInterface
{
    /**
     * @param string $primarySourceId
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validatePrimarySource(
        string $primarySourceId,
        \Magento\Quote\Api\Data\CartInterface $cart
    ): void;
}
