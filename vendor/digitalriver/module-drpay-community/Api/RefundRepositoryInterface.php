<?php declare(strict_types=1);
/**
 * Refund Repository Interface
 *
 * @summary
 * Provides CRUD operations for Refund
 * @author
 *
 * @category Digitalriver
 * @package Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Api;

use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Digitalriver\DrPay\Api\Data\RefundInterface;

/**
 * Digital River Refund CRUD interface.
 */
interface RefundRepositoryInterface
{
    /**
     * Save Refund Record.
     *
     * @param RefundInterface $refund
     *
     * @return void
     * @throws LocalizedException
     */
    public function save(RefundInterface $refund): void;

    /**
     * Retrieve Refund data by id.
     *
     * @param string $drRefundId
     *
     * @return RefundInterface
     * @throws LocalizedException
     */
    public function getById(string $drRefundId): RefundInterface;

    /**
     * Retrieve Refund matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return SearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;

    /**
     * Delete Refund.
     *
     * @param RefundInterface $refund
     *
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(RefundInterface $refund): bool;

    /**
     * Delete Refund by ID.
     *
     * @param string $drRefundId
     *
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(string $drRefundId): bool;

    /**
     * Saves a Refund
     *
     * @param string $drRefundId
     * @param float $amount
     */
    public function saveRefund(
        string $drRefundId,
        float $amount
    ): void;
}
