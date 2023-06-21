<?php
/**
 * @category Digitalriver
 * @package: Digitalriver_DrPay
 *
 */

namespace Digitalriver\DrPay\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Digitalriver\DrPay\Helper\Data as DrData;

class OrderRefundObserver implements ObserverInterface
{

    /**
     *
     * @param Drdata $drHelper
     */
    public function __construct(
        DrData $drHelper
    ) {
        $this->drHelper = $drHelper;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @throws LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();

	$refundRequestResult = $this->drHelper->setRefundRequest($creditmemo);

        if (isset($refundRequestResult['error']) && !$refundRequestResult['error']){
                $refundedAmount = $refundRequestResult['refundedAmount'];

                $order = $creditmemo->getOrder();
                $creditmemo->getOrder()->setDrTotalRefund($order->getDrTotalRefund() + $refundedAmount);
        } else {
            $errorMessage = isset($refundRequestResult['message']) ? $refundRequestResult['message'] : 'set Refund Request failed';
            throw new LocalizedException(__($errorMessage));
        }

        return $this;
    }
}
