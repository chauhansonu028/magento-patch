<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2020 Classy Llama Studios, LLC
 */

namespace Zendesk\Zendesk\Observer;

use Magento\Framework\Event\Observer;
use Zendesk\Zendesk\Model\Config\ConfigProvider;
use Zendesk\Zendesk\Helper\Sunshine;

class CartItemRemove extends Base
{

    /**
     * Event name: sales_quote_remove_item
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->isEnabled(ConfigProvider::XML_PATH_EVENT_CART_REMOVE_ITEMS)) {
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
            $item = $this->observer->getQuoteItem();

            $payload = [
                'event' => [
                    'created_at' => date('c'),
                    'description' => 'Item removed from cart',
                    'properties' => [
                        'properties' => [
                            'title' => $item->getName(),
                            'quantity' => number_format($item->getQtyOrdered() ?? $item->getQty(), 0, '.', ','),
                            'price' => number_format($item->getPrice(), 2, '.', ',')
                        ],
                    ],
                    'source' => Sunshine::IDENTIFIER,
                    'type' => 'cart item remove'
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
                    'source' => Sunshine::IDENTIFIER,
                    'type' => Sunshine::PROFILE_TYPE
                ]
            ];

            return $payload;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            $this->logError($exception->getMessage());
            return [];
        }
    }
}
