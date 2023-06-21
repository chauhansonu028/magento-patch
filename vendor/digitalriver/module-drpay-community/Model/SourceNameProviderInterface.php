<?php

namespace Digitalriver\DrPay\Model;

interface SourceNameProviderInterface
{
    /**
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @return array
     */
    public function getSourceNameFromQuote(\Magento\Quote\Api\Data\CartInterface $cart): array;
}
