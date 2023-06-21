<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Api;

use Digitalriver\DrPay\Api\Data\PlaceOrderResultInterface;

/**
 * Interface QuoteManagementGuestInterface
 *
 * Interface to handle Digital River guest order placement through web API
 */
interface GuestQuoteManagementInterface
{
    /**
     * @param string $cartId
     * @param string $checkoutId
     * @param string $paymentSessionId
     * @param string|null $primarySourceId
     * @param bool $updateCheckout
     * @param string $savedSourceId
     * @return PlaceOrderResultInterface
     */
    public function placeOrder(
        string $cartId,
        string $checkoutId,
        string $paymentSessionId,
        ?string $primarySourceId = null,
        bool $updateCheckout = true,
        ?string $savedSourceId = null
    ): PlaceOrderResultInterface;
}
