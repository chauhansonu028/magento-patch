<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2020 Classy Llama Studios, LLC
 */

namespace Zendesk\Zendesk\Observer;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Event\Observer;
use Zendesk\Zendesk\Helper\Sunshine;
use Zendesk\Zendesk\Model\Config\ConfigProvider;

class CustomerAddressSave extends Base
{

    /**
     * @inheritdoc
     *
     * Event name: customer_address_save_after
     * From the Sunshine point of view, this is just the customer updated event.
     */
    public function execute(Observer $observer)
    {
        if (!$this->isEnabled(ConfigProvider::XML_PATH_EVENT_CUSTOMER_CREATE_UPDATE)) {
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
            $customer = $this->observer->getDataObject()->getCustomer();

            $payload = [
                'event' => [
                    'created_at' => date('c'),
                    'description' => 'customer updated',
                    'properties' => [
                        'status' => 'customer updated'
                    ],
                    'source' => Sunshine::IDENTIFIER,
                    'type' => 'customer updated'
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
        $addresses = $this->observer->getDataObject()->getCustomer()->getAddresses();
        // If the address that was changed, is the shipping address, use that address.
        if ($this->observer->getCustomerAddress()->getDefaultShipping()) {
            return $this->observer->getCustomerAddress();
        }
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
