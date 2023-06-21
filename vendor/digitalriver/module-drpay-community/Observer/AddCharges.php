<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Observer;

use Digitalriver\DrPay\Helper\Config;
use Digitalriver\DrPay\Api\ChargeRepositoryInterface;
use Digitalriver\DrPay\Api\Data\ChargeInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class AddCharges implements ObserverInterface
{
    /**
     * @var config
     */
    private $config;

    /**
     * @var ChargeRepositoryInterface
     */
    private $chargeRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * AddCharges constructor.
     *
     * @param Config $config
     * @param ChargeRepositoryInterface $chargeRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Config $config,
        ChargeRepositoryInterface $chargeRepository,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->config = $config;
        $this->chargeRepository = $chargeRepository;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @see order_accepted_webhook event
     * @see \Digitalriver\DrPay\Model\Sales\QuoteManagement::saveCharges
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->getIsEnabled()) {
            return;
        }

        /** @var array $webhookObject */
        $webhookObject = $observer->getDataByKey('webhook_object');
        if (!$this->isObjectValid($webhookObject)) {
            return;
        }
        /** @var OrderInterface $order */
        $order = $this->getOrder((int)$webhookObject['id'], $webhookObject['upstreamId']);
        if ($order !== null) {
            $sources = $this->normalizeSources($webhookObject['payment']['sources']);
            $charges = $webhookObject['payment']['charges'];
            foreach ($charges as $charge) {
                if (!$this->doesChargeExist($charge['id'])) {
                    $sourceId = $charge['sourceId'];
                    $source = $sources[$sourceId];
                    $this->chargeRepository->saveCharge(
                        $charge['id'],
                        (int)$order->getEntityId(),
                        $webhookObject['id'],
                        $sourceId,
                        $source['type'],
                        floatval($charge['amount'])
                    );
                }
            }
        }
    }

    /**
     * @param string $drChargeId
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function doesChargeExist(string $drChargeId): bool
    {
        $this->searchCriteriaBuilder->addFilter(ChargeInterface::DR_CHARGE_ID, $drChargeId);
        $this->searchCriteriaBuilder->setPageSize(1);
        return $this->chargeRepository->getList($this->searchCriteriaBuilder->create())->getTotalCount() > 0;
    }

    /**
     * @param int $drOrderId
     * @param string $incrementId
     *
     * @return OrderInterface|null
     */
    private function getOrder(int $drOrderId, string $incrementId): ?OrderInterface
    {
        $this->searchCriteriaBuilder->addFilter('dr_order_id', $drOrderId);
        $this->searchCriteriaBuilder->addFilter(OrderInterface::INCREMENT_ID, $incrementId);
        $this->searchCriteriaBuilder->setPageSize(1);
        $orderList = $this->orderRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        if (!empty($orderList)) {
            return current($orderList);
        }
        return null;
    }

    /**
     * @param array $sources
     *
     * @return array
     */
    private function normalizeSources(array $sources): array
    {
        $normalizedSources = [];
        foreach ($sources as $source) {
            $normalizedSources[$source['id']] = $source;
        }
        return $normalizedSources;
    }

    /**
     * @param array $webhookObject
     *
     * @return bool
     */
    private function isObjectValid(array $webhookObject): bool
    {
        return !empty($webhookObject['payment']) &&
            isset(
                $webhookObject['payment']['charges'],
                $webhookObject['payment']['sources'],
                $webhookObject['id'],
                $webhookObject['upstreamId']
            );
    }
}
