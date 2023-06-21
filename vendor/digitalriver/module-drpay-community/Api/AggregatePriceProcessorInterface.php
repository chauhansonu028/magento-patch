<?php

namespace Digitalriver\DrPay\Api;

use Magento\Quote\Api\Data\CartItemInterface;

/**
 *  Interface AggregatePriceProcessorInterface
 */
interface AggregatePriceProcessorInterface
{
    /**
     * @param  CartItemInterface$item
     * @return float
     */
    public function getAggregatePrice(CartItemInterface $item): float;
}
