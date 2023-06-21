<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DataServices\ViewModel\Checkout;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * ViewModel for Checkout Success Context
 */
class SuccessContextProvider implements ArgumentInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var PriceCurrencyInterface
     */
    private PriceCurrencyInterface $priceCurrency;

    /**
     * @param CheckoutSession $checkoutSession
     * @param Json $jsonSerializer
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        Json $jsonSerializer,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->jsonSerializer = $jsonSerializer;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Return cart id for event tracking
     *
     * @return int
     */
    public function getCartId() : int
    {
        return (int) $this->checkoutSession->getLastRealOrder()->getQuoteId();
    }

    /**
     * Return customer email for event tracking
     *
     * @return string
     */
    public function getCustomerEmail() : string
    {
        return $this->checkoutSession->getLastRealOrder()->getCustomerEmail();
    }

    /**
     * Return order id for event tracking
     *
     * @return string
     */
    public function getOrderId() : string
    {
        return $this->checkoutSession->getLastRealOrder()->getIncrementId();
    }

    /**
     * Return payment method data
     *
     * @return string
     */
    public function getPayment(): string
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $payment = $order->getPayment();
        $paymentData = [
            'total' => $this->priceCurrency->convertAndRound(
                $order->getBaseGrandTotal(),
                $order->getStore()
            ),
            'paymentMethodCode' => $payment->getMethod(),
            'paymentMethodName' => $payment->getMethodInstance()->getTitle()
        ];
        return $this->jsonSerializer->serialize([$paymentData]);
    }

    /**
     * Return shipping data
     *
     * @return string
     */
    public function getShipping(): string
    {
        return $this->jsonSerializer->serialize(
            [
                'shippingMethod' => $this->checkoutSession->getLastRealOrder()->getShippingMethod(),
                'shippingAmount' => $this->checkoutSession->getLastRealOrder()->getShippingAmount(),
            ]
        );
    }
}
