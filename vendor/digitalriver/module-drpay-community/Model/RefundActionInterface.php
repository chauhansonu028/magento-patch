<?php

namespace Digitalriver\DrPay\Model;

interface RefundActionInterface
{
    /**
     * @param \Digitalriver\DrPay\Api\Data\ChargeInterface $charge
     * @param array $refund
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    public function performRefundAction(
        \Digitalriver\DrPay\Api\Data\ChargeInterface $charge,
        array $refund,
        \Magento\Sales\Api\Data\OrderInterface $order
    ): void;
}
