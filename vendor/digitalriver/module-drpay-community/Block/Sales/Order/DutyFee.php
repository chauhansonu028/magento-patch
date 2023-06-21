<?php
/**
 * Display Duty Fee and IOR Tax value in Frontend Sales Invoice tab
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Block\Sales\Order;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order;

/**
 * Get Duty fee and IOR tax value from the sales_order table.
 */
class DutyFee extends Template
{
    /**
     * Initialize Duty Fee and IOR Tax order total
     *
     * @return DutyFee
     */
    public function initTotals(): DutyFee
    {
        if ($this->getOrder()->getDrDutyFee() === null &&
            $this->getOrder()->getDrIorTax() === null) {
            return $this;
        }
        $total = new DataObject(
            [
                'code' => $this->getNameInLayout(),
                'block_name' => $this->getNameInLayout(),
                'area' => $this->getArea(),
            ]
        );
        $after = $this->getAfterTotal();
        if (!$after) {
            $after = 'tax';
        }
        $this->getParentBlock()->addTotal($total, $after);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Return order.
     *
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * @return string
     */
    public function getLabelProperties(): string
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * @return string
     */
    public function getValueProperties(): string
    {
        return $this->getParentBlock()->getValueProperties();
    }
}
