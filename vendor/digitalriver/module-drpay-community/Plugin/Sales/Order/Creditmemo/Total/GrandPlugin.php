<?php
/**
 * Plugin for Duty fee and IOR tax.
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Plugin\Sales\Order\Creditmemo\Total;

/**
 * Class GrandPlugin
\ */
class GrandPlugin
{
    /**
     * Adds IOR tax and Duty fee to totals
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Total\Grand $grand
     * @return $this
     */
    public function afterCollect(\Magento\Sales\Model\Order\Creditmemo\Total\Grand $grand, $result, $creditmemo)
    {
        if ($creditmemo->getOrder()->getBaseDrDutyFee() !== null
            && $creditmemo->getOrder()->getBaseDrIorTax() !== null) {
            $grandTotal = $creditmemo->getGrandTotal();
            $baseGrandTotal = $creditmemo->getBaseGrandTotal();

            /*Gets the value of duty fee that are refunded in previous credit memo*/
            $collection = $creditmemo->getOrder()->getCreditmemosCollection();
            $refundedDutyFee = 0;
            foreach ($collection as $item) {
                $refundedDutyFee += $item->getData('base_dr_duty_fee');
            }
            $maxRefundableDutyFee = $creditmemo->getOrder()->getBaseDrDutyFee() - $refundedDutyFee;

            $maxRefundableDutyFee = round($maxRefundableDutyFee, 3);
            $creditMemoBaseDrDutyFee = $creditmemo->getBaseDrDutyFee() ?? 0;
            $creditmemoDutyFee = round($creditMemoBaseDrDutyFee, 3);

            if ($creditmemoDutyFee > $maxRefundableDutyFee) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Maximum Duty fee amount allowed to refund is: %1', $maxRefundableDutyFee)
                );
            }

            /*Duty fee belonging to particular order*/
            $originalDutyFee = $creditmemo->getOrder()->getDrDutyFee();
            $baseDutyFee = $creditmemo->getOrder()->getBaseDrDutyFee();

            /*Converts the base duty fee into original duty fee*/
            $ratio = 0;
            if ($baseDutyFee > 0) {
                $ratio = $creditmemoDutyFee / $baseDutyFee;
            }
            $finalCreditMemoDutyFee = $originalDutyFee * $ratio;
            $finalCreditMemoDutyFee = round($finalCreditMemoDutyFee, 2);

            $creditmemo->setDrDutyFee($finalCreditMemoDutyFee);
            $creditmemo->setBaseDrDutyFee($creditmemo->getBaseDrDutyFee());

            $grandTotal += $finalCreditMemoDutyFee;
            $baseGrandTotal += $creditmemo->getBaseDrDutyFee();

            /*Logic for adding IOR tax*/
            if ($creditmemo->getBaseDrIorTax() > 0) {
                $creditmemo->setDrIorTax($creditmemo->getOrder()->getDrIorTax());
                $creditmemo->setBaseDrIorTax($creditmemo->getBaseDrIorTax());
                $grandTotal += $creditmemo->getDrIorTax();
                $baseGrandTotal += $creditmemo->getBaseDrIorTax();
            }

            $creditmemo->setGrandTotal($grandTotal);
            $creditmemo->setBaseGrandTotal($baseGrandTotal);
        }
        return $result;
    }
}
