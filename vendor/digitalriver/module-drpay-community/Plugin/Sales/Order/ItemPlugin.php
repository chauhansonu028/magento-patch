<?php

/**
 * Cancel DR Fulfillment when entire order cancellation or partial order item cancellation is done in Magento
 */

namespace Digitalriver\DrPay\Plugin\Sales\Order;

class ItemPlugin
{
    /**
     * @var \Digitalriver\DrPay\Helper\Data $drHelper
     */
    protected $helper;
    /**
     * @var \Digitalriver\DrPay\Helper\Data $drHelper
     */
    protected $logger;

    public function __construct(
        \Digitalriver\DrPay\Helper\Data $drHelper,
        \Digitalriver\DrPay\Logger\Logger $logger
    ) {
        $this->drHelper = $drHelper;
        $this->_logger  = $logger;
    }

    /**
     * This function to used to get the canceled qty from $item->cancel() execution
     * \Magento\Sales\Model\Order\Item
     *
     * @param object $subject
     * @param object $result
     *
     * @return $result
     */
    public function afterCancel(
        \Magento\Sales\Model\Order\Item $subject,
        $result
    ) {
        if ($subject->getQtyCanceled() > 0) {
            $drItemId   = $subject->getDrOrderLineitemId();
            $order      = $subject->getOrder();
            $drOrderId  = $order->getDrOrderId();

            // DR line item id is empty for some child/parent line items
            if (!empty($drItemId) && !empty($drOrderId)) {
                $items[$drItemId] = [
                "requisitionID"             => $drOrderId,
                "noticeExternalReferenceID" => $order->getIncrementId(),
                "lineItemID"                => $drItemId,
                "quantity"                  => $subject->getQtyCanceled(),
                "sku"                        => $subject->getSku()
                ];

                $this->drHelper->setFulfillmentCancellation($items, $order);
            }
        }
        return $result;
    } // end: function afterCancel
}
