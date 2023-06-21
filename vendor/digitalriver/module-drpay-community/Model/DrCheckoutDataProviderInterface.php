<?php

namespace Digitalriver\DrPay\Model;

interface DrCheckoutDataProviderInterface
{
    /**
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @return array
     */
    public function provideCheckoutData(\Magento\Quote\Api\Data\CartInterface $cart): array;
}
