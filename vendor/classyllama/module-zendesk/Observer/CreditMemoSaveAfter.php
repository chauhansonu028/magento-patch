<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2020 Classy Llama Studios, LLC
 */

namespace Zendesk\Zendesk\Observer;

use Magento\Framework\Event\Observer;
use Zendesk\Zendesk\Model\Config\ConfigProvider;
use Zendesk\Zendesk\Helper\Sunshine;

class CreditMemoSaveAfter extends Base
{

    /**
     * Event name: sales_order_creditmemo_save_after
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->isEnabled(ConfigProvider::XML_PATH_EVENT_REFUND_STATUS)) {
            return;
        }
        $this->observer = $observer;

        // check if user was logged in
        if ($this->observer->getCreditmemo()->getOrder()->getCustomerId() === null) {
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
            $items = $this->observer->getCreditmemo()->getItems();
            $itemsArray = $this->makeItemArray($items);
            return [
                'event' => [
                    'created_at' => date('c'),
                    'description' => 'refund created',
                    'properties' => [
                        'refund_line_items' => $itemsArray,
                        'total refunded' => $this->observer->getCreditmemo()->getGrandTotal()
                    ],
                    'source' => Sunshine::IDENTIFIER,
                    'type' => 'refund created'
                ],
                'profile' => [
                    'identifiers' => [
                        [
                            'type' => 'email',
                            'value' => $this->observer->getCreditmemo()->getOrder()->getCustomerEmail()
                        ],
                        [
                            'type' => 'id',
                            'value' => (string)$this->observer->getCreditmemo()->getOrder()->getCustomerId()
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

    /**
     * Make item array
     *
     * @param array $items
     * @return array
     */
    protected function makeItemArray($items)
    {
        $itemArray = [];
        foreach ($items as $item) {
            $orderItemId = $item->getOrderItemId();
            $orderItems = $this->observer->getCreditmemo()->getOrder()->getitems();
            if (array_key_exists($orderItemId, $orderItems)) {
                $hasParent = $this->observer->getCreditmemo()->getOrder()->getitems()[$orderItemId]->getParentItem();
            } else {
                $hasParent = $item->getParentItem();
            }
            // Only add to array, if it doesn't have a parent product.
            if (!$hasParent) {
                $data = [
                    'name' => $item->getName(),
                    'qty' => number_format($item->getQtyRefunded() ?? $item->getQty(), 0, '.', ','),
                    'price' => number_format($item->getPriceInclTax(), 2, '.', ',')
                ];
                if ($data['qty']) {
                    $itemArray [] = $data;
                }
            }
        }

        return $itemArray;
    }
}
