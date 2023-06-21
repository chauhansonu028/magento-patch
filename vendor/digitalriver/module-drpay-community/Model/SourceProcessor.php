<?php
/**
 * DR Sources management model
 *
 * @summary Provides methods for managing DR sources on the backend.
 * @author Vujadin Scepanovic <vujadin.scepanovic@rs.ey.com>
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Model;

class SourceProcessor
{
    const ERROR_CODE_MISSING_CHECKOUT_ID = 1;

    /**
     * @var \Digitalriver\DrPay\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Digitalriver\DrPay\Logger\Logger
     */
    protected $checkoutSession;

    /**
     * Constructor
     *
     * @param \Digitalriver\DrPay\Logger\Logger     $logger
     * @param \Magento\Checkout\Model\Session       $checkoutSession
     */
    public function __construct(
        \Digitalriver\DrPay\Logger\Logger $logger,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }

    /**
     * Locks the DR Checkout ID
     *
     * @return bool
     */
    public function lockCheckoutId()
    {
        $checkoutId = $this->checkoutSession->getDrCheckoutId();
        $drQuoteError = $this->checkoutSession->getDrQuoteError();

        if (!isset($checkoutId) || $drQuoteError) {
            throw new \Magento\Framework\Exception\InvalidArgumentException(
                __('Something went wrong'),
                null,
                self::ERROR_CODE_MISSING_CHECKOUT_ID
            );
        }
        $this->checkoutSession->setDrLockedInCheckoutId($checkoutId);

        return true;
    }
}
