<?php

namespace Digitalriver\DrPay\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Digitalriver\DrPay\Helper\Data as DrHelper;
use Magento\Framework\Exception\LocalizedException;

class DrCheckoutManagement
{
    /** @var int */
    private const MAX_ATTEMPTS = 2000;

    /** @var CheckoutSession */
    private $checkoutSession;

    /** @var DrHelper */
    private $drHelper;

    public function __construct(
        CheckoutSession $checkoutSession,
        DrHelper        $drHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->drHelper = $drHelper;
    }

    /**
     * @param $quote
     * @return void
     */
    public function setCheckoutByQuote($quote)
    {
        try {
            $counter = 0;

            do {
                $counter++;

                $isLocked = $this->checkoutSession->getDrCheckoutLock();
                if ($isLocked) {
                    continue;
                }

                $this->checkoutSession->setDrCheckoutLock(true);
                $this->drHelper->setCheckout($quote);
                $this->checkoutSession->setDrCheckoutLock(false);
                break;
            } while ($counter < self::MAX_ATTEMPTS);

            if ($counter === self::MAX_ATTEMPTS) {
                throw new LocalizedException(__('Maximum lock attempts reached'));
            }
        } catch (\Exception $e) {

        } finally {
            $this->checkoutSession->setDrCheckoutLock(false);
        }
    }
}
