<?php

namespace Zendesk\Zendesk\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Zendesk\Zendesk\Api\Data\OrderInterface;
use Zendesk\Zendesk\Api\Data\OrderInterfaceFactory;
use Zendesk\Zendesk\Helper\Config;
use Zendesk\Zendesk\Helper\Data;

class OrderRepository implements \Zendesk\Zendesk\Api\OrderRepositoryInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var OrderInterfaceFactory
     */
    protected $zendeskOrderFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * OrderRepository constructor.
     * @param OrderInterfaceFactory $zendeskOrderFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param Data $helper
     * @param Config $configHelper
     */
    public function __construct(
        OrderInterfaceFactory $zendeskOrderFactory, // @phpstan-ignore-line
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        Data $helper,
        Config $configHelper
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->zendeskOrderFactory = $zendeskOrderFactory;
        $this->helper = $helper;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->configHelper = $configHelper;
    }

    /**
     * @inheritdoc
     */
    public function getOrders($emailAddress, $brandId, $orderCount)
    {
        $this->searchCriteriaBuilder->addFilter(
            \Magento\Sales\Api\Data\OrderInterface::CUSTOMER_EMAIL,
            $emailAddress
        );

        $storeIds = $this->configHelper->getBrandStores($brandId);
        if (!empty($storeIds)) {
            $this->searchCriteriaBuilder->addFilter(
                \Magento\Sales\Api\Data\OrderInterface::STORE_ID,
                $storeIds,
                'in'
            );
        }

        $this->sortOrderBuilder->setField(\Magento\Sales\Api\Data\OrderInterface::CREATED_AT);
        $this->sortOrderBuilder->setDescendingDirection();

        $this->searchCriteriaBuilder->addSortOrder($this->sortOrderBuilder->create());

        $this->searchCriteriaBuilder->setPageSize($orderCount);

        $orders = $this->orderRepository->getList($this->searchCriteriaBuilder->create())->getItems();

        /** @var OrderInterface[] $zendeskOrders */
        $zendeskOrders = [];

        foreach ($orders as $order) {
            $zendeskOrder = $this->zendeskOrderFactory->create();

            $zendeskOrder->setOrderUrl($this->helper->getOrderDeepLinkUrl($order->getEntityId()));

            if (!empty($order->getCustomerId())) {
                $zendeskOrder->setCustomerBackendUrl($this->helper->getCustomerDeepLinkUrl($order->getCustomerId()));
            }

            $zendeskOrder->setOrder($order);

            $zendeskOrders[] = $zendeskOrder;
        }

        return $zendeskOrders;
    }
}
