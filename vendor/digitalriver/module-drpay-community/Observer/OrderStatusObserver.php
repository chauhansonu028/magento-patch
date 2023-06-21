<?php

/**
 * Mark the order as complete
 */

namespace Digitalriver\DrPay\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class OrderStatusObserver implements ObserverInterface
{
    /**
     *
     * @param \Digitalriver\DrPay\Helper\Data $drHelper
     */
    public function __construct(
        \Digitalriver\DrPay\Helper\Data $drHelper
    ) {
        $this->drHelper = $drHelper;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // This is a COM API Only state change. DR API completions are handled thru webhooks.
        $order = $observer->getEvent()->getOrder();
        if ($order instanceof \Magento\Framework\Model\AbstractModel) {
            if ($order->getState() == Order::STATE_COMPLETE) {
                $result = $this->drHelper->setOrderStateComplete($order);
                if ($result) {
                    $comment = "Order complete";
                    $order->addStatusToHistory($order->getState(), __($comment));
                    $order->save();
                }
            }
        }
        return $this;
    }
}
