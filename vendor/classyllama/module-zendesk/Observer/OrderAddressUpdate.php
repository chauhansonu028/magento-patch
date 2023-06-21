<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2020 Classy Llama Studios, LLC
 */

namespace Zendesk\Zendesk\Observer;

use Magento\Framework\Event\Observer;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order;
use Zendesk\Zendesk\Helper\Sunshine;
use Zendesk\Zendesk\Model\Config\ConfigProvider;

class OrderAddressUpdate extends Base
{

    /**
     * @inheritdoc
     *
     * Event name: sales_order_place_after
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
            // parent ID is the order ID
            /** @var Order $order */
            $order = $this->observer->getAddress()->getOrder();
            $customerId = $order->getCustomerId();
            if (!$customerId) {
                // This means the customer is not logged in/does not have an account.
                return [];
            }
            $items = $order->getItems();
            $itemArray = $this->makeItemArray($items);

            $payload = [
                'event' => [
                    'created_at' => date('c'),
                    'description' => 'order updated',
                    'properties' => [
                        'line_items' => $itemArray,
                        'total price' => number_format($order->getGrandTotal(), 2, '.', ','),
                        'fulfilment status.' => $order->getStatus(),
                        'Order ID' => $order->getIncrementId()
                    ],
                    'source' => Sunshine::IDENTIFIER,
                    'type' => 'order updated'
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
                        'first name' => $order->getCustomerFirstname(),
                        'last name' => $order->getCustomerLastname(),
                        'orders count' => $this->getTotalOrders($customerId),
                        'total spent' => $this->getTotalSpent($customerId)
                    ],
                    'source' => Sunshine::IDENTIFIER,
                    'type' => Sunshine::PROFILE_TYPE
                ]
            ];
            // add values that might not have a value, so that that I can only add them if they exist.
            if ($shippingAddress = $order->getShippingAddress()) {
                $shippingAddress->getTelephone() ? $payload['profile']['attributes']['phone'] =
                    $shippingAddress->getTelephone() : null;
                $shippingData = $this->getArrayShippingAddress($shippingAddress);
                $payload['profile']['attributes']['address'] =  $shippingData;
                $payload['event']['properties']['shipping address'] = $shippingData;
            }

            return $payload;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            $this->logError($exception->getMessage());
            return [];
        }
    }

    /**
     * Get shipping address array
     *
     * @param OrderAddressInterface $address
     * @return array|null
     * Changed name to make it allowable to pass argument when the parent method doesn't.
     */
    protected function getArrayShippingAddress($address)
    {
        $addressArray = [];
        foreach ($address->getStreet() as $i => $street) {
            $addressArray['address' . (string)($i+1)] = $street;
        }
        $address->getCity() ? $addressArray['city'] = $address->getCity() : null;
        $address->getRegion() ? $addressArray['province'] = $address->getRegion() : null;
        $address->getCountryId() ? $addressArray['country'] = $this->getCountryName($address->getCountryId()) : null;
        $address->getPostcode() ? $addressArray['zip'] = $address->getPostcode() : null;
        return $addressArray;
    }
}
