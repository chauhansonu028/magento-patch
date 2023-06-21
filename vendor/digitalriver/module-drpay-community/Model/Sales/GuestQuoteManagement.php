<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\Sales;

use Digitalriver\DrPay\Api\Data\PlaceOrderResultInterface;
use Digitalriver\DrPay\Api\GuestQuoteManagementInterface;
use Digitalriver\DrPay\Api\QuoteManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResource;

/**
 * Class QuoteManagementGuest
 *
 * Handles Digital River guest order placement process
 */
class GuestQuoteManagement implements GuestQuoteManagementInterface
{
    /**
     * @var QuoteManagementInterface
     */
    private $quoteManagement;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var QuoteIdMaskResource
     */
    private $quoteIdMaskResource;

    /**
     * @param QuoteManagementInterface $quoteManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param QuoteIdMaskResource $quoteIdMaskResource
     */
    public function __construct(
        QuoteManagementInterface $quoteManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMaskResource $quoteIdMaskResource
    ) {
        $this->quoteManagement = $quoteManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResource = $quoteIdMaskResource;
    }

    /**
     * @param string $cartId
     * @param string $checkoutId
     * @param string $paymentSessionId
     * @param string|null $primarySourceId
     * @param bool $updateCheckout
     * @param string|null $savedSourceId
     * @return PlaceOrderResultInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function placeOrder(
        string $cartId,
        string $checkoutId,
        string $paymentSessionId,
        ?string $primarySourceId = null,
        bool $updateCheckout = true,
        ?string $savedSourceId = null
    ): PlaceOrderResultInterface {
        return $this->quoteManagement->placeOrder(
            $this->retrieveCartId($cartId),
            $checkoutId,
            $paymentSessionId,
            $primarySourceId,
            $updateCheckout,
            $savedSourceId
        );
    }

    /**
     * @param string $cartId
     * @return int
     * @throws NoSuchEntityException
     */
    private function retrieveCartId(string $cartId): int
    {
        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $this->quoteIdMaskResource->load($quoteIdMask, $cartId, 'masked_id');
        $realCartId = (int)$quoteIdMask->getQuoteId();
        if (!$realCartId) {
            throw NoSuchEntityException::singleField('masked_id', $cartId);
        }
        return $realCartId;
    }
}
