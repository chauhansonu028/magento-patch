<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2020 Classy Llama Studios, LLC
 */

namespace Zendesk\Zendesk\Observer;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Event\Observer;
use Zendesk\Zendesk\Model\Config\ConfigProvider;
use Zendesk\Zendesk\Helper\Sunshine;

class CustomerSave extends Base
{
    // variables for text on account created vs updated.
    public const UPDATE_TITLE = "customer updated";
    public const CREATE_TITLE = "customer created";

    /**
     * Event name: sales_order_place_after
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->isEnabled(ConfigProvider::XML_PATH_EVENT_CUSTOMER_CREATE_UPDATE)) {
            return;
        }
        $this->observer = $observer;
        $this->observerType = $observer->getEvent()->getname();
        $this->eventName = $this->getEventType();
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
            $customer = $this->observer->getCustomer();

            $payload = [
                'event' => [
                    'created_at' => date('c'),
                    'description' => $this->eventName,
                    'properties' => [
                        'status' => $this->eventName
                    ],
                    'source' => Sunshine::IDENTIFIER,
                    'type' => $this->eventName
                ],
                'profile' => [
                    'identifiers' => [
                        [
                            'type' => 'email',
                            'value' => $customer->getEmail()
                        ],
                        [
                            'type'=> 'id',
                            'value' => (string)$customer->getEntityId()
                        ]
                    ],
                    'attributes' => [
                        'first name' => $customer->getFirstname(),
                        'last name' => $customer->getLastname(),
                        'orders count' => $this->getTotalOrders($customer->getEntityId()),
                        'total spent' => $this->getTotalSpent($customer->getEntityId())
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
        } catch (\Exception $exception) {
            $this->logError($exception->getMessage());
            return [];
        }
    }

    /**
     * Get shipping address
     *
     * @return array|AddressInterface|mixed
     */
    protected function getShippingAddress()
    {
        $addresses = $this->observer->getCustomer()->getAddresses();
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
     * Get event type
     *
     * @return string
     */
    protected function getEventType()
    {
        $customer = $this->observer->getCustomer();
        // If the customer was created at the same time it was updated, then the customer was just created.
        // Otherwise, it was just updated.
        if ($customer->getCreatedAt() === $customer->getUpdatedAt()) {
            return self::CREATE_TITLE;
        } else {
            return self::UPDATE_TITLE;
        }
    }

    /**
     * Get shipping address array
     *
     * @return array|null
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
}
