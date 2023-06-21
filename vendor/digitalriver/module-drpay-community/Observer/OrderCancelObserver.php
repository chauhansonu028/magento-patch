<?php

/**
 * Update EFN status once order is canceled
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 * @author   Mohandass <mohandass.unnikrishnan@diconium.com>
 *
 */

namespace Digitalriver\DrPay\Observer;

use Digitalriver\DrPay\Helper\Data;
use Digitalriver\DrPay\Logger\Logger;
use Digitalriver\DrPay\Model\DrConnectorFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class OrderCancelObserver implements ObserverInterface
{
    /**
     * Event name for Order Cancel
     */
    const EVENT_ORDER_CANCEL_AFTER   = 'order_cancel_after';
    /**
     * @var Digitalriver\DrPay\Model\DrConnectorFactory
     */
    protected $drFactory;

    /**
     *
     * @param Data $drHelper
     * @param DrConnectorFactory
     * @param Logger
     *
     */
    public function __construct(
        DrConnectorFactory $drFactory,
        Data $drHelper,
        Logger $logger
    ) {
        $this->drFactory    = $drFactory;
        $this->drHelper     = $drHelper;
        $this->_logger      = $logger;
    }

    /**
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $items = [];

        try {
            $order = $observer->getEvent()->getOrder();

            if ($order->getDrOrderId()) {
                // If order is canceled or complete, Update EFN Post Status column
                if ($order->getState() == Order::STATE_CANCELED || $order->getState() == Order::STATE_COMPLETE) {
                    $drModel = $this->drFactory->create()->load($order->getDrOrderId(), 'requisition_id');
                    $drModel->setPostStatus(1);
                    $drModel->save();
                } // end: if
            } // end: if
        } catch (\Exception $ex) {
            $this->_logger->error('OrderCancelObserver Error : ' . $ex->getMessage());
        } // end: try
    }
}
