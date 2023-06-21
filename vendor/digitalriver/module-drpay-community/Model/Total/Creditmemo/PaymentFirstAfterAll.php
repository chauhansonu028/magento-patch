<?php

namespace Digitalriver\DrPay\Model\Total\Creditmemo;

/**
 * Calculates credit memo totals
 */
class PaymentFirstAfterAll extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{

    /**
     * Collect customer balance totals for credit memo
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $totalPaid = $order->getTotalPaid();
        $baseTotalPaid = $order->getBaseTotalPaid();
        $totalRefunded = $order->getBaseTotalRefunded();
        $baseTotalRefunded = $order->getBaseTotalRefunded();
        $possibleReturnToPayment = $totalPaid - $totalRefunded;
        $basePossibleReturnToPayment = $baseTotalPaid - $baseTotalRefunded;

        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $basePossibleReturnToPayment);
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $possibleReturnToPayment);

        return $this;
    }
}
