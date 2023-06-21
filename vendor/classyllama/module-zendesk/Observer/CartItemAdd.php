<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2020 Classy Llama Studios, LLC
 */

namespace Zendesk\Zendesk\Observer;

use Magento\Framework\Event\Observer;
use Zendesk\Zendesk\Model\Config\ConfigProvider;
use Zendesk\Zendesk\Helper\Sunshine;

class CartItemAdd extends Base
{
    public const NEW_CART_TEXT = 'cart created';
    public const CART_UPDATE_TEXT = 'cart item add';

    /**
     * Event
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->isEnabled(ConfigProvider::XML_PATH_EVENT_CART_ADD_ITEMS)) {
            return;
        }
        // If the user is not logged in, don't do anything.
        if (!$this->isLoggedIn()) {
            return;
        }
        $this->observer = $observer;

        try {
            $this->createEvent();
        } catch (\Exception $exception) {
            $this->logError($exception->getMessage());
            return;
        }
    }

    /**
     * @inheritdoc
     */
    protected function getSunshineEvent()
    {
        try {
            $item = $this->observer->getProduct();

            $payload = [
                'event' => [
                    'created_at' => date('c'),
                    'description' => $this->isCartNew() ? self::NEW_CART_TEXT : self::CART_UPDATE_TEXT,
                    'properties' => [
                        'title' => $item->getName(),
                        'quantity' => number_format($item->getQtyOrdered() ?? $item->getQty(), 0, '.', ','),
                        'price' => number_format($item->getFinalPrice(), 2, '.', ',')
                    ],
                    'source' => Sunshine::IDENTIFIER,
                    'type' => $this->isCartNew() ? self::NEW_CART_TEXT : self::CART_UPDATE_TEXT
                ],
                'profile' => [
                    'identifiers' => [
                        [
                            'type' => 'email',
                            'value' => $this->getCustomerEmail()
                        ],
                        [
                            'type'=> 'id',
                            'value' => (string)$this->getCustomerId()
                        ]
                    ],
                    'attributes' => [
                        'first name' => $this->_customerSession->getCustomer()->getFirstname(),
                        'last name' => $this->_customerSession->getCustomer()->getLastname(),
                    ],
                    'source' => Sunshine::IDENTIFIER,
                    'type' => Sunshine::PROFILE_TYPE
                ]
            ];

            // add values that might not have a value, so that that I can only add them if they exist.
            $this->getShippingAddress() && $this->getShippingAddress()->getTelephone()
                ? $payload['profile']['attributes']['phone'] = $this->getShippingAddress()->getTelephone() : null;
            $this->getShippingAddressArray() ? $payload['profile']['attributes']['address'] =
                $this->getShippingAddressArray() : null;

            return $payload;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            $this->logError($exception->getMessage());
            return [];
        }
    }

    /**
     * Is cart is new
     *
     * @return bool
     */
    protected function isCartNew()
    {
        try {
            // get items from cart in session
            $sessionCartContents = $this->checkoutSession->getQuote()->getitems();
            // Get items from quote attached to item.
            $cartContents = $this->observer->getQuoteItem()->getQuote()->getAllItems();
            $item = $this->observer->getQuoteItem();
        } catch (\Exception $exception) {
            $this->logError($exception->getMessage());
            $cartContents = false;
        }
        // The cart is new, if there is not items in the session cart,
        // and the item added to the cart, is the same as the first item in the quote.
        // (for configurable or bundled products, this is the main product)
        if (!$sessionCartContents && $item == array_shift($cartContents)) {
            return true;
        } else {
            return false;
        }
    }
}
