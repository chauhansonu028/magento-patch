<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2020 Classy Llama Studios, LLC
 */

namespace Zendesk\Zendesk\Observer;

use Magento\Framework\Event\Observer;
use Zendesk\Zendesk\Model\Config\ConfigProvider;
use Zendesk\Zendesk\Helper\Sunshine;

class CheckoutBegin extends Base
{
    /**
     * Event name: controller_action_predispatch_checkout_index_index
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->isEnabled(ConfigProvider::XML_PATH_EVENT_CHECKOUT_BEGIN)) {
            return;
        }
        // If the user is not logged in, don't do anything.
        if (!$this->isLoggedIn()) {
            return;
        }

        $this->observer = $observer;
        $this->observerType = $observer->getEvent()->getname();

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
            $cart = $this->getCart();
            $items = $cart->getItems();
            $itemArray = $this->makeItemArray($items);

            $payload = [
                'event' => [
                    'created_at' => date('c'),
                    'description' => 'Checkout Started',
                    'properties' => [
                        'line_items' => $itemArray,
                        'total price' => number_format($cart->getGrandTotal(), 2, '.', ',')
                    ],
                    'source' => Sunshine::IDENTIFIER,
                    'type' => 'checkouts_create'
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
                        'orders count' => $this->getTotalOrders($this->getCustomerId()),
                        'total spent' => $this->getTotalSpent($this->getCustomerId())
                    ],
                    'source' => Sunshine::IDENTIFIER,
                    'type' => Sunshine::PROFILE_TYPE
                ]
            ];
            // add values that might not have a value, so that that I can only add them if they exist.
            $this->getShippingAddress() && $this->getShippingAddress()->getTelephone() ?
                $payload['profile']['attributes']['phone'] = $this->getShippingAddress()->getTelephone() : null;
            $this->getShippingAddressArray() ? $payload['profile']['attributes']['address'] =
                $this->getShippingAddressArray() : null;

            return $payload;
        } catch (\Exception $exception) {
            $this->logError($exception->getMessage());
            return [];
        }
    }
}
