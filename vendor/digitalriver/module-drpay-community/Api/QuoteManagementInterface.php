<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Api;

use Digitalriver\DrPay\Api\Data\PlaceOrderResultInterface;

/**
 * Interface QuoteManagementInterface
 *
 * Interface to handle Digital River order placement through web API
 */
interface QuoteManagementInterface
{
    /**
     * @param int $cartId
     * @param string $checkoutId
     * @param string $paymentSessionId
     * @param string|null $primarySourceId
     * @param bool $updateCheckout
     * @param string $savedSourceId
     *
     * @return PlaceOrderResultInterface
     */
    public function placeOrder(
        int $cartId,
        string $checkoutId,
        string $paymentSessionId,
        ?string $primarySourceId = null,
        bool $updateCheckout = true,
        ?string $savedSourceId = null
    ): PlaceOrderResultInterface;

    /**
     * @return array
     */
    public function getLastApiResult(): array;
}
