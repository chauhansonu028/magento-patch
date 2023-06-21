<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2020 Classy Llama Studios, LLC
 */

namespace Zendesk\Zendesk\Observer;

use Magento\Framework\Event\Observer;
use Zendesk\Zendesk\Model\Config\ConfigProvider;
use Zendesk\Zendesk\Helper\Sunshine;

class CustomerDelete extends Base
{

    /**
     * Event name: sales_order_place_after
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->isEnabled(ConfigProvider::XML_PATH_EVENT_CUSTOMER_DELETE)) {
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
            $customer = $this->observer->getCustomer();

            return [
                'event' => [
                    'created_at' => date('c'),
                    'description' => 'Customer deleted',
                    'properties' => [
                        'status' => 'Customer Deleted'
                    ],
                    'source' => Sunshine::IDENTIFIER,
                    'type' => 'customer delete'
                ],
                'profile' => [
                    'identifiers' => [
                        [
                            'type'=> 'id',
                            'value' => (string)$customer->getEntityId()
                        ]
                    ],
                    'source' => Sunshine::IDENTIFIER,
                    'type' => Sunshine::PROFILE_TYPE
                ]
            ];
        } catch (\Exception $exception) {
            $this->logError($exception->getMessage());
            return [];
        }
    }
}
