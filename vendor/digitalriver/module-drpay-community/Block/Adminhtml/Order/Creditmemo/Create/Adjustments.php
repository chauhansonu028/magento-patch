<?php
/**
 * Formats the float value
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Block\Adminhtml\Order\Creditmemo\Create;

use Magento\Sales\Model\Order;
use Magento\Framework\Currency;

/**
 * Credit memo adjustments block
 *
 */
class Adjustments extends \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Adjustments
{
    /**
     * Formats the price value
     *
     * @param $value
     * @return string
     */
    public function formatValue($value): string
    {
        /** @var Order $order */
        $order = $this->getSource()->getOrder();

        return $order->getOrderCurrency()->formatPrecision(
            $value,
            2,
            ['display' => Currency::NO_SYMBOL],
            false,
            false
        );
    }

    /**
     * @param $value
     * @return float|int
     */
    public function round($value = 0)
    {
        return $this->priceCurrency->round($value) * 1;
    }
}
