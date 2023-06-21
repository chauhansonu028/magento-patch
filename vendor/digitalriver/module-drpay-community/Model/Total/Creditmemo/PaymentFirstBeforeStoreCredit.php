<?php

namespace Digitalriver\DrPay\Model\Total\Creditmemo;

/**
 * Calculates credit memo totals
 */
class PaymentFirstBeforeStoreCredit extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{

    /**
     * Here we calculate $possibleReturnToPayment as difference between total_paid and total_refunded
     * Then we deduct grand_total for this value.
     * We reserve it and will add back later after store credit and giftcardaccount totals are calculated
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

        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $basePossibleReturnToPayment);
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $possibleReturnToPayment);

        return $this;
    }
}
