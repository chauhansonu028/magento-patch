<?php
/**
 * Display Duty Fees in Invoice PDF page.
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\Sales\Pdf;

use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;

/**
 * Display Duty fees in PDF Invoice
 */
class DutyFees extends DefaultTotal
{
    /**
     * Get array of arrays with totals information for display in PDF
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     * @return array
     */
    public function getTotalsForDisplay(): array
    {
        $dutyFee = $this->getOrder()->getDrDutyFee();
        $iorTax = $this->getOrder()->getDrIorTax();
        if ($dutyFee === null && $iorTax === null) {
            return [];
        }
        return parent::getTotalsForDisplay();
    }
}
