<?php
/**
 * Displays IOR Tax and Duty Fee
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Block\Sales\Order\Creditmemo;

use Magento\Sales\Block\Order\Creditmemo\Totals as TotalsBlock;

/**
 * Adds Duty Fee and Ior tax
 */
class Totals extends TotalsBlock
{
    /**
     * Initialize order totals array
     *
     * @return $this
     */
    protected function _initTotals()
    {
        parent::_initTotals();

        if ($this->getSource()->getOrder()->getDrDutyFee() != null ||
            $this->getSource()->getOrder()->getDrIorTax() != null) {
            $this->addTotal(
                new \Magento\Framework\DataObject(
                    [
                        'code' => 'dr_duty_fee',
                        'value' => $this->getSource()->getDrDutyFee(),
                        'label' => __('Duty Fees'),
                    ]
                )
            );
            $this->addTotal(
                new \Magento\Framework\DataObject(
                    [
                        'code' => 'dr_ior_tax',
                        'value' => $this->getSource()->getDrIorTax(),
                        'label' => __('IOR Tax'),
                    ]
                )
            );
        }
        return $this;
    }
}
