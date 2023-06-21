<?php

/**
 * Order Invoice Register Observer
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 * @author   Mohandass <mohandass.unnikrishnan@diconium.com>
 *
 */

namespace Digitalriver\DrPay\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderFulfillmentUpdateToDr implements ObserverInterface
{
    /**
     * Event name for Invoice Save
     */
    private const EVENT_INVOICE_REGISTER = 'sales_order_invoice_register';

    /**
     * @var \Digitalriver\DrPay\Helper\Data
     */
    private $drHelper;

    /**
     * @var \Digitalriver\DrPay\Logger\Logger
     */
    private $_logger;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonSerializer;

    public function __construct(
        \Digitalriver\DrPay\Helper\Data $drHelper,
        \Digitalriver\DrPay\Logger\Logger $logger,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
    ) {
        $this->drHelper = $drHelper;
        $this->_logger  = $logger;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $items = [];

        try {
            $event = $observer->getEvent()->getName();
            $order = $observer->getEvent()->getInvoice()->getOrder();

            if (!empty($event)) {
                if ($event == self::EVENT_INVOICE_REGISTER) {
                    $items = $this->_getInvoiceDetails($observer->getEvent()->getInvoice());
                } // end: if
            } // end: if
            $this->drHelper->logger('EVENT_INVOICE_REGISTER');
            $this->drHelper->logger($this->jsonSerializer->serialize($items));

            if (!empty($items)) {
                $this->drHelper->setFulfillmentRequest($items, $order);
            }
        } catch (\Exception $ex) {
            $this->_logger->error('setFulfillmentRequest Error : ' . $ex->getMessage());
        } // end: try
    }

    /**
     * Collect the invoice details from observer and process line items
     *
     * @param object $invoiceObj
     *
     * @return array $items
     *
     */
    private function _getInvoiceDetails($invoiceObj)
    {
        $items = [];

        try {
            foreach ($invoiceObj->getItems() as $invoiceItem) {
                $orderItem  = $invoiceItem->getOrderItem();
                $order      = $orderItem->getOrder();
                $isVirtual  = $orderItem->getIsVirtual();
                $lineItemId = $orderItem->getDrOrderLineitemId();
                $qty        = (int)$invoiceItem->getQty();

                if ($qty < 1 || empty($lineItemId) || !$isVirtual) {
                    continue;
                }

                $items[$lineItemId] = [
                    "requisitionID"             => $order->getDrOrderId(),
                    "noticeExternalReferenceID" => $order->getIncrementId(),
                    "lineItemID"                => $lineItemId,
                    "quantity"                  => $qty,
                    "sku"                       => $orderItem->getSku()
                ];
            } // end: foreach
        } catch (\Exception $ex) {
            $this->_logger->error('Error from _getInvoiceDetails(): ' . $ex->getMessage());
        } // end: try

        return $items;
    } // end: function _getInvoiceDetails
}
