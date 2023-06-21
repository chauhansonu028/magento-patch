<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2020 Classy Llama Studios, LLC
 */

namespace Zendesk\Zendesk\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class AllCustomerOrders extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * AllCustomerOrders constructor.
     *
     * @param Context $context
     * @param CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        Context $context,
        CollectionFactory $orderCollectionFactory
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Get all orders for a specific customer ID
     *
     * @param string|int $customerId
     * @return array
     */
    public function getOrderCollection($customerId)
    {
        $customerOrder = $this->orderCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId);
        return $customerOrder->getData();
    }

    /**
     * Get total orders number
     *
     * @param string|int $id
     * @return int|void
     */
    public function getTotalNumberOrders($id)
    {
        $orders = $this->getOrderCollection($id);
        return count($orders);
    }

    /**
     * Get total spent value
     *
     * @param string|int $id
     * @return string
     */
    public function getTotalSpent($id)
    {
        $orders = $this->getOrderCollection($id);
        $totalPrice = 0;
        foreach ($orders as $order) {
            $totalPrice += $order['total_paid'] ?? $order['grand_total'];
        }

        return(number_format($totalPrice, 2, '.', ','));
    }
}
