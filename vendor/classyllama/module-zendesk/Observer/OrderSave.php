<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2020 Classy Llama Studios, LLC
 */

namespace Zendesk\Zendesk\Observer;

use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Zendesk\Zendesk\Helper\Sunshine;
use Zendesk\Zendesk\Model\Config\ConfigProvider;

class OrderSave extends Base
{
    public const CREATETEXT = "order created";
    public const UPDATETEXT = 'order updated';

    /**
     * Event name: sales_order_place_after
     *
     * This event handles order created and order updated
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->isEnabled(ConfigProvider::XML_PATH_EVENT_ORDER_CREATE_UPDATE)) {
            return;
        }

        $this->observer = $observer;
        $this->observerType = $observer->getEvent()->getname();

        // check if user was logged in
        if ($this->observer->getOrder()->getCustomerId() === null) {
            return;
        }
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
            $customerId = $this->observer->getOrder()->getCustomerId();
            if (!$customerId) {
                // This means the customer is not logged in/does not have an account.
                return [];
            }
            $customer = $this->getCustomerById($customerId);
            $order = $this->observer->getOrder();
            $items = $order->getItems();
            $itemArray = $this->makeItemArray($items);

            $payload = [
                'event' => [
                    'created_at' => date('c'),
                    'description' => $this->getEventType($order),
                    'properties' => [
                        'line_items' => $itemArray,
                        'total price' => number_format($order->getGrandTotal(), 2, '.', ','),
                        'fulfilment status.' => $order->getStatus(),
                        'Order ID' => $order->getIncrementId()
                    ],
                    'source' => Sunshine::IDENTIFIER,
                    'type' => $this->getEventType($order)
                ],
                'profile' => [
                    'identifiers' => [
                        [
                            'type' => 'email',
                            'value' => $order->getCustomerEmail()
                        ],
                        [
                            'type'=> 'id',
                            'value' => (string)$order->getCustomerId()
                        ]
                    ],
                    'attributes' => [
                        'first name' => $customer->getFirstname(),
                        'last name' => $customer->getLastname(),
                        'orders count' => $this->getTotalOrders($customer->getId()),
                        'total spent' => $this->getTotalSpent($customer->getId())
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
            $this->getShippingAddressArray() ? $payload['event']['properties']['shipping address'] =
                $this->getShippingAddressArray() : null;

            return $payload;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            $this->logError($exception->getMessage());
            return [];
        }
    }

    /**
     * @inheritdoc
     */
    protected function getCustomerEmail()
    {
        $this->observer->getOrder()->getEmail();
        return $this->_customerSession->getCustomer()->getEmail();
    }

    /**
     * @inheritdoc
     */
    protected function getShippingAddress()
    {
        $addresses = $this->observer->getOrder()->getAddresses();
        if (count($addresses) === 1) {
            return array_shift($addresses);
        }
        foreach ($addresses as $address) {
            if ($address->getAddressType() == 'shipping') {
                return $address;
            }
        }
        return end($addresses);
    }

    /**
     * @inheritdoc
     */
    protected function getShippingAddressArray()
    {
        $address = $this->getShippingAddress();
        if (!$address) {
            return null;
        }
        $addressArray = [];
        $address->getStreet()[0] ? $addressArray['address1'] = $address->getStreet()[0] : null;
        count($address->getStreet()) > 1 ? $addressArray['address2'] = $address->getStreet()[1] : null;
        $address->getCity() ? $addressArray['city'] = $address->getCity() : null;
        $address->getRegion() ? $addressArray['province'] = $address->getRegion() : null;
        $address->getCountryId() ? $addressArray['country'] = $this->getCountryName($address->getCountryId()) : null;
        $address->getPostcode() ? $addressArray['zip'] = $address->getPostcode() : null;
        return $addressArray;
    }

    /**
     * Get event type
     *
     * @param Order $order
     * @return string
     */
    protected function getEventType($order)
    {
        if ($order->getCreatedAt() === $order->getUpdatedAt()) {
            return self::CREATETEXT;
        } else {
            return self::UPDATETEXT;
        }
    }
}
