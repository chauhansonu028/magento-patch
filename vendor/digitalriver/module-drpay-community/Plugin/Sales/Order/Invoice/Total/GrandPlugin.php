<?php
/**
 * Plugin for Duty fee and IOR tax.
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Plugin\Sales\Order\Invoice\Total;

/**
 * Class GrandPlugin
\ */
class GrandPlugin
{

    /**
     * Adds IOR tax and Duty fee to totals
     *
     * @param \Magento\Sales\Model\Order\Invoice\Total\Grand $grand
     * @return $this
     */
    public function afterCollect(\Magento\Sales\Model\Order\Invoice\Total\Grand $grand, $result, $invoice)
    {

        $order = $invoice->getOrder();
        $invoiceCollection = $order->getInvoiceCollection();

        $drIorTax = $order->getDrIorTax();
        $drBaseIorTax = $order->getBaseDrIorTax();
        $drDutyFee = $order->getDrDutyFee();
        $drBaseDutyFee = $order->getBaseDrDutyFee();

        $invoice->setDrIorTax(null);
        $invoice->setBaseDrIorTax(null);
        $invoice->setDrDutyFee(null);
        $invoice->setBaseDrDutyFee(null);

        // only include IOR and Duty on the first invoice
        if ($invoiceCollection->getTotalCount() === 0 && ($drIorTax || $drDutyFee)) {

            $grandTotal = $invoice->getGrandTotal();
            $baseGrandTotal = $invoice->getBaseGrandTotal();

            $grandTotal = $grandTotal+ $drIorTax + $drDutyFee;
            $baseGrandTotal += $drBaseIorTax + $drBaseDutyFee;

            $invoice->setDrIorTax($drIorTax);
            $invoice->setBaseDrIorTax($drBaseIorTax);

            $invoice->setDrDutyFee($drDutyFee);
            $invoice->setBaseDrDutyFee($drBaseDutyFee);

            $invoice->setGrandTotal($grandTotal);
            $invoice->setBaseGrandTotal($baseGrandTotal);
        }

        return $result;
    }
}
