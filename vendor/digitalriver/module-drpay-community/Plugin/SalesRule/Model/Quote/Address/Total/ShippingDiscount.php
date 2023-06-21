<?php
/**
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Plugin\SalesRule\Model\Quote\Address\Total;

use Digitalriver\DrPay\Helper\Data;
use Digitalriver\DrPay\Logger\Logger;
use Digitalriver\DrPay\Model\DrCheckoutManagement;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;

class ShippingDiscount
{
    /**
     * Maximum log attempts
     */
    const  MAX_LOCK_ATTEMPTS = 20000;

    /** @var Session  */
    private $checkoutSession;

    /** @var Data  */
    private $drHelper;

    /** @var Logger  */
    private $logger;

    /** @var DrCheckoutManagement  */
    private $drCheckoutManagement;

    public function __construct(
        Data $drHelper,
        Logger $logger,
        Session $checkoutSession,
        DrCheckoutManagement $drCheckoutManagement
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->drHelper = $drHelper;
        $this->drCheckoutManagement = $drCheckoutManagement;
    }

    public function afterCollect(
        \Magento\SalesRule\Model\Quote\Address\Total\ShippingDiscount $subject,
        $result,
        $quote,
        $shippingAssignment,
        $total
    ) {
        $items = $shippingAssignment->getItems();
        if (empty($items)) {
            return $result;
        }

        $this->logger->info("SETTING CHECKOUT.");
        $this->drCheckoutManagement->setCheckoutByQuote($quote);
        $this->logger->info("DONE SETTING CHECKOUT.");

        return $result;
    }
}
