<?php
/**
 * Register shipment request
 */

namespace Digitalriver\DrPay\Plugin\Sales\Order;

use Exception;
use Magento\Framework\Exception\LocalizedException;

class ShipmentPlugin
{
    /**
     * @var \Digitalriver\DrPay\Helper\Data $drHelper
     */
    protected $helper;

    /**
     * @var \Digitalriver\DrPay\Helper\Data $drHelper
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $json;

    public function __construct(
        \Digitalriver\DrPay\Helper\Data $drHelper,
        \Digitalriver\DrPay\Logger\Logger $logger,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->drHelper = $drHelper;
        $this->_logger  = $logger;
        $this->json = $json;
    }

    /**
     * This function to used to get the shipped qty from register functionality
     * \Magento\Sales\Model\Order\Shipment
     *
     * @param object $subject
     * @param object $result
     *
     * @return $result
     */
    public function afterRegister(
        \Magento\Sales\Model\Order\Shipment $subject,
        $result
    ) {
        $items = [];

        if ($subject->getId()) {
            return $result;
        } // end: if

        try {
            $track = $subject->getTracksCollection()->getFirstItem();
            $carrierCode = $track->getCarrierCode();
            $trackingNumber=$track->getTrackNumber();
            
            foreach ($subject->getItems() as $shipmentItem) {
                $orderItem  = $shipmentItem->getOrderItem();
                $order      = $orderItem->getOrder();
                $lineItemId = $orderItem->getDrOrderLineitemId();
                $qty        = (int)$shipmentItem->getQty();

                if ($qty < 1 || empty($lineItemId)) {
                    continue;
                }

                $items[$lineItemId] = [
                    "requisitionID"             => $order->getDrOrderId(),
                    "noticeExternalReferenceID" => $order->getIncrementId(),
                    "lineItemID"                => $lineItemId,
                    "quantity"                  => $qty,
                    "sku"                       => $orderItem->getSku(),
                    "trackingCompany"           => $carrierCode,
                    "trackingNumber"            => $trackingNumber
                ];
            }

            if (empty($items)) {
                return $result;
            }

            $this->drHelper->setFulfillmentRequest($items, $subject->getOrder());
        } catch (LocalizedException $exception) {
            $this->_logger->error('Error afterRegister : ' . $this->json->serialize($exception->getRawMessage()));
        } catch (Exception $exception) {
            $this->_logger->error('Error afterRegister : ' . $exception->getMessage());
        }

        return $result;
    }
}
