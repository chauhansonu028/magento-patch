<?php

namespace Digitalriver\DrPay\Model;

class RefundActionPool
{
    /**
     * @var RefundActionInterface[]
     */
    private $actions;

    public function __construct(
        array $actions = []
    ) {
        foreach ($actions as $action) {
            if (!($action instanceof RefundActionInterface)) {
                throw new \Magento\Framework\Exception\InvalidArgumentException(
                    __('Provided RefundAction is not the correct type.')
                );
            }
        }

        $this->actions = $actions;
    }

    /**
     * @param \Digitalriver\DrPay\Api\Data\ChargeInterface $charge
     * @param array $refund
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    public function execute(
        \Digitalriver\DrPay\Api\Data\ChargeInterface $charge,
        array $refund,
        \Magento\Sales\Api\Data\OrderInterface $order
    ): void {
        foreach ($this->actions as $action) {
            $action->performRefundAction($charge, $refund, $order);
        }
    }
}
