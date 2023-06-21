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

class OrderShipped extends Base
{
    public const FEDEX_TRACKING_URL = "https://www.fedex.com/apps/fedextrack/?action=track&action=track&tracknumbers=";
    public const USPS_TRACKING_URL = "https://tools.usps.com/go/TrackConfirmAction?tLabels=";
    public const UPS_TRACKING_URL = "https://www.ups.com/WebTracking?tracknum=";
    public const DHL_TRACKING_URL_BEFORE = "https://www.dhl.com/en/express/tracking.html?AWB=";
    public const DHL_TRACKING_URL_AFTER = "&brand=DHL";

    /**
     * Event name: sales_order_place_after
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->isEnabled(ConfigProvider::XML_PATH_EVENT_ORDER_SHIPPED)) {
            return;
        }

        $this->observer = $observer;
        $this->observerType = $observer->getEvent()->getname();

        // check if user was logged in
        if ($this->observer->getShipment()->getOrder()->getCustomerId() === null) {
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
            $order = $this->observer->getShipment()->getOrder();
            $trackingNumbers = $this->getTrackingNumbers($order);

            // This has to be created in a seperate variable for array_shift to work,
            // and getting the 0th element doesn't work because it's often not at index 0
            $addresses = null;
            $trackData= $this->observer->getShipment()->getTracks();
            $customerId = $this->observer->getshipment()->getCustomerId();
            $customer = $this->getCustomerById($customerId);

            $payload = [
                'event' => [
                    'created_at' => date('c'),
                    'description' => 'Order Shipped',
                    'properties' => [
                        'order id' => $order->getId(),
                        'shipment status' => $order->getStatus(),
                        'Order ID' => $order->getIncrementId()
                    ],
                    'source' => Sunshine::IDENTIFIER,
                    'type' => 'Order shipped'
                ],
                'profile' => [
                    'identifiers' => [
                        [
                            'type' => 'email',
                            'value' => $customer->getEmail()
                        ]
                    ],
                    'attributes' => [
                        'first name' => $customer->getFirstname(),
                        'last name' => $customer->getLastname(),
                    ],
                    'source' => Sunshine::IDENTIFIER,
                    'type' => Sunshine::PROFILE_TYPE
                ]
            ];
            // add values that might not have a value, so that that I can only add them if they exist.
            $this->getTrackingNumbers($trackData) ? $payload['event']['properties']['tracking numbers'] =
                $this->getTrackingNumbers($trackData) : null;
            $this->getTrackingUrls($trackData) ? $payload['event']['properties']['tracking urls'] =
                $this->getTrackingurls($trackData) : null;
            $this->getCarriers($trackData) ? $payload['event']['properties']['carriers'] =
                $this->getCarriers($trackData) : null;

            $this->getShippingAddressArray() ? $payload['event']['properties']['destination'] =
                $this->getShippingAddressArray() : null;
            return $payload;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            $this->logError($exception->getMessage());
            return [];
        }
    }

    /**
     * Get customer email
     *
     * @return string
     */
    protected function getCustomerEmail()
    {
        $this->observer->getOrder()->getEmail();
        return $this->_customerSession->getCustomer()->getEmail();
    }

    /**
     * Get shipping address
     *
     * @return array|AddressInterface|mixed
     */
    protected function getShippingAddress()
    {
        $addresses = $this->observer->getShipment()->getOrder()->getAddresses();
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
     * Get tracking numbers
     *
     * @param array $trackData
     * @return array
     */
    protected function getTrackingNumbers($trackData)
    {
        $trackArray = [];
        foreach ($trackData as $track) {
            $trackArray[] = $track->getData('track_number');
        }

        return $trackArray;
    }

    /**
     * Get carrier
     *
     * @param array $trackData
     * @return array
     */
    protected function getCarriers($trackData)
    {
        $carrierArray = [];
        foreach ($trackData as $track) {
            $carrierArray[] = $track->getCarrierCode();
        }

        return $carrierArray;
    }

    /**
     * Get tracking URLs
     *
     * @param array $trackingData
     * @return array
     */
    protected function getTrackingUrls($trackingData)
    {
        // TODO: get actual tracking urls. This just returns an array of tracking numbers.
        $trackingUrls = [];
        foreach ($trackingData as $track) {
            $number = $track->getData('track_number');

            // If it is one of the specified carriers, create a url, and add to the list.
            if ($track->getCarrierCode() === "fedex") {
                $trackingUrls[] = self::FEDEX_TRACKING_URL . $number;
            } elseif ($track->getCarrierCode() === "ups") {
                $trackingUrls[] = self::UPS_TRACKING_URL . $number;
            } elseif ($track->getCarrierCode() === "dhl") {
                $trackingUrls[] = self::DHL_TRACKING_URL_BEFORE . $number . self::DHL_TRACKING_URL_AFTER;
            } elseif ($track->getCarrierCode() === "usps") {
                $trackingUrls[] = self::USPS_TRACKING_URL . $number;
            }
        }

        return $trackingUrls;
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
