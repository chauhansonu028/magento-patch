<?php

declare(strict_types=1);

namespace Digitalriver\DrPay\Model\OfflineRefund;

use Digitalriver\DrPay\Api\Data\OfflineRefundInterface;
use Digitalriver\DrPay\Api\Data\OfflineRefundSearchResultInterface;
use Digitalriver\DrPay\Api\OfflineRefundRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Cached
 **/
class RepositoryCached implements OfflineRefundRepositoryInterface
{
    /**
     * @var OfflineRefundInterface[]
     */
    private $instancesByCreditmemoId = [];

    /**
     * @var OfflineRefundInterface[]
     */
    private $instancesByRefundId = [];

    /**
     * @var OfflineRefundRepositoryInterface
     */
    private $offlineRefundRepository;

    /**
     * Cached constructor.
     * @param OfflineRefundRepositoryInterface $offlineRefundRepository
     */
    public function __construct(
        OfflineRefundRepositoryInterface $offlineRefundRepository
    ) {
        $this->offlineRefundRepository = $offlineRefundRepository;
    }

    /**
     * Save OfflineRefund Record.
     *
     * @param OfflineRefundInterface $offlineRefund
     *
     * @return OfflineRefundInterface
     * @throws CouldNotSaveException
     */
    public function save(OfflineRefundInterface $offlineRefund): OfflineRefundInterface
    {
        $offlineRefund = $this->offlineRefundRepository->save($offlineRefund);
        $this->removeFromCache($offlineRefund);
        return $offlineRefund;
    }

    /**
     * Load OfflineRefund data by given id
     *
     * @param string $drOfflineRefundId
     *
     * @return OfflineRefundInterface
     * @throws NoSuchEntityException
     */
    public function get(string $drOfflineRefundId): OfflineRefundInterface
    {
        if (!array_key_exists($drOfflineRefundId, $this->instancesByRefundId)) {
            try {
                $this->addToCache($this->offlineRefundRepository->get($drOfflineRefundId));
            } catch (NoSuchEntityException $e) {
                $this->instancesByRefundId[$drOfflineRefundId] = null;
                throw $e;
            }
        } elseif ($this->instancesByRefundId[$drOfflineRefundId] === null) {
            throw NoSuchEntityException::singleField(
                OfflineRefundInterface::FIELD_DR_REFUND_ID,
                $drOfflineRefundId
            );
        }

        return $this->instancesByRefundId[$drOfflineRefundId];
    }

    /**
     * Load OfflineRefund data by given id
     *
     * @param string $drOfflineRefundId
     *
     * @return bool
     */
    public function checkExistence(string $drOfflineRefundId): bool
    {
        if (array_key_exists($drOfflineRefundId, $this->instancesByRefundId)) {
            return true;
        } else {
            return $this->offlineRefundRepository->checkExistence($drOfflineRefundId);
        }
    }

    /**
     * Find OfflineRefunds by SearchCriteria
     *
     * @param SearchCriteriaInterface $criteria
     *
     * @return OfflineRefundSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $criteria): OfflineRefundSearchResultInterface
    {
        $searchResults = $this->offlineRefundRepository->getList($criteria);

        foreach ($searchResults->getItems() as $offlineRefund) {
            $this->addToCache($offlineRefund);
        }

        return $searchResults;
    }

    /**
     * Delete OfflineRefund record
     *
     * @param OfflineRefundInterface $offlineRefund
     *
     * @return void
     * @throws CouldNotDeleteException
     */
    public function delete(OfflineRefundInterface $offlineRefund): void
    {
        $this->removeFromCache($offlineRefund);
        $this->offlineRefundRepository->delete($offlineRefund);
    }

    /**
     * Get by creditmemo ID
     *
     * @param int $creditmemoId
     * @return OfflineRefundInterface
     * @throws NoSuchEntityException
     */
    public function getByCreditmemoId(int $creditmemoId): OfflineRefundInterface
    {
        if (!array_key_exists($creditmemoId, $this->instancesByCreditmemoId)) {
            try {
                $this->addToCache($this->offlineRefundRepository->getByCreditmemoId($creditmemoId));
            } catch (NoSuchEntityException $e) {
                $this->instancesByCreditmemoId[$creditmemoId] = null;
                throw $e;
            }
        } elseif ($this->instancesByCreditmemoId[$creditmemoId] === null) {
            throw NoSuchEntityException::singleField(OfflineRefundInterface::FIELD_CREDIT_MEMO_ID, $creditmemoId);
        }

        return $this->instancesByCreditmemoId[$creditmemoId];
    }

    /**
     * @param OfflineRefundInterface $offlineRefund
     */
    private function addToCache(OfflineRefundInterface $offlineRefund): void
    {
        $this->instancesByCreditmemoId[$offlineRefund->getCreditmemoId()] = $offlineRefund;
        $this->instancesByRefundId[$offlineRefund->getDrRefundId()] = $offlineRefund;
    }

    /**
     * @param OfflineRefundInterface $offlineRefund
     */
    private function removeFromCache(OfflineRefundInterface $offlineRefund): void
    {
        unset($this->instancesByCreditmemoId[$offlineRefund->getCreditmemoId()]);
        unset($this->instancesByRefundId[$offlineRefund->getDrRefundId()]);
    }
}
