<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Api;

use Digitalriver\DrPay\Api\Data\OfflineRefundInterface;
use Digitalriver\DrPay\Api\Data\OfflineRefundSearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface OfflineRefundRepositoryInterface to perform CRUD Operation
 */
interface OfflineRefundRepositoryInterface
{
    /**
     * Save OfflineRefund Record.
     *
     * @param OfflineRefundInterface $offlineRefund
     *
     * @return OfflineRefundInterface
     * @throws CouldNotSaveException
     */
    public function save(OfflineRefundInterface $offlineRefund): OfflineRefundInterface;

    /**
     * Load OfflineRefund data by given id
     *
     * @param string $drOfflineRefundId
     *
     * @return OfflineRefundInterface
     * @throws NoSuchEntityException
     */
    public function get(string $drOfflineRefundId): OfflineRefundInterface;

    /**
     * Load OfflineRefund data by given id
     *
     * @param string $drOfflineRefundId
     *
     * @return bool
     */
    public function checkExistence(string $drOfflineRefundId): bool;

    /**
     * Find OfflineRefunds by SearchCriteria
     *
     * @param SearchCriteriaInterface $criteria
     *
     * @return OfflineRefundSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $criteria): OfflineRefundSearchResultInterface;

    /**
     * Delete OfflineRefund record
     *
     * @param OfflineRefundInterface $offlineRefund
     *
     * @return void
     * @throws CouldNotDeleteException
     */
    public function delete(OfflineRefundInterface $offlineRefund): void;

    /**
     * Get by creditmemo ID
     *
     * @param int $creditmemoId
     * @return OfflineRefundInterface
     * @throws NoSuchEntityException
     */
    public function getByCreditmemoId(int $creditmemoId): OfflineRefundInterface;
}
