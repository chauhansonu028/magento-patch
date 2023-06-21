<?php

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Api\QuoteManagementInterface;
use Digitalriver\DrPay\Helper\Data;
use Digitalriver\DrPay\Logger\Logger;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Create DR Order
 */
class DrOrderManagement
{
    /**
     * @var Session
     */
    private $checkoutSession;
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var QuoteManagementInterface
     */
    private $quoteManagement;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var PrimarySourceValidatorPool
     */
    private $primarySourceValidatorPool;
    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * DrOrderManagement constructor.
     * @param ManagerInterface $messageManager
     * @param Session $checkoutSession
     * @param Data $helper
     * @param Logger $logger
     * @param PrimarySourceValidatorPool $primarySourceValidatorPool
     * @param QuoteManagementInterface $quoteManagement
     * @param Json $jsonSerializer
     */
    public function __construct(
        ManagerInterface $messageManager,
        Session $checkoutSession,
        Data $helper,
        Logger $logger,
        PrimarySourceValidatorPool $primarySourceValidatorPool,
        QuoteManagementInterface $quoteManagement,
        Json $jsonSerializer
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
        $this->quoteManagement = $quoteManagement;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
        $this->primarySourceValidatorPool = $primarySourceValidatorPool;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function placeOrder()
    {
        try {
            $cartId = $this->checkoutSession->getQuoteId();
            $checkoutId = $this->checkoutSession->getDrLockedInCheckoutId();
            if (empty($checkoutId)) {
                $this->messageManager->addErrorMessage(__('Unable to Place Order'));
                return false;
            }

            $paymentSessionId = $this->checkoutSession->getDrPaymentSessionId();
            if (empty($paymentSessionId)) {
                $this->messageManager->addErrorMessage(__('Unable to Place Order'));
                return false;
            }

            $primarySourceId = $this->checkoutSession->getDrSourceId();

            $placeOrderResult =
                $this->quoteManagement->placeOrder($cartId, $checkoutId, $paymentSessionId, $primarySourceId);
            return $placeOrderResult->getOrderId();
        } catch (LocalizedException $le) {
            $this->logger->error('Payment Error : ' . $this->jsonSerializer->serialize($le->getLogMessage()));
            $this->messageManager->addError($le->getMessage());
            // If exception thrown from DR calls, then $result may be emtpy which will lead to another error
            $apiResult = $this->quoteManagement->getLastApiResult();
            if (!empty($apiResult)) {
                $this->helper->setOrderCancellation($apiResult);
            } // end: if
            return false;
        } catch (\Exception $ex) {
            $this->logger->error('Payment Error : ' . $this->jsonSerializer->serialize($ex->getMessage()));
            $this->messageManager->addError(__('Sorry! An error occurred, Try again later.'));
            // If exception thrown from DR calls, then $result may be emtpy which will lead to another error
            $apiResult = $this->quoteManagement->getLastApiResult();
            if (!empty($apiResult)) {
                $this->helper->setOrderCancellation($apiResult);
            } // end: if

            return false;
        } // end: try
    }
}
