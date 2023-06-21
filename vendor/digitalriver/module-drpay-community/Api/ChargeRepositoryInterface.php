<?php declare(strict_types=1);
/**
 * Charge Repository Interface
 *
 * @summary
 * Provides CRUD operations for Charges
 * @author Vujadin Scepanovic <vujadin.scepanovic@rs.ey.com>
 *
 * @category Digitalriver
 * @package Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Api;

use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Digitalriver\DrPay\Api\Data\ChargeInterface;

/**
 * Digital River Charge CRUD interface.
 */
interface ChargeRepositoryInterface
{
    /**
     * Save Charge Record.
     *
     * @param ChargeInterface $charge
     *
     * @return void
     * @throws LocalizedException
     */
    public function save(ChargeInterface $charge): void;

    /**
     * Retrieve Charge data by id.
     *
     * @param int $chargeId
     *
     * @return ChargeInterface
     * @throws LocalizedException
     */
    public function getById(int $chargeId): ChargeInterface;

    /**
     * Retrieve Charge matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return SearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;

    /**
     * Delete Charge.
     *
     * @param ChargeInterface $charge
     *
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(ChargeInterface $charge): bool;

    /**
     * Delete Charge by ID.
     *
     * @param int $syncId
     *
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $syncId): bool;

    /**
     * Saves a Charge
     *
     * @param string $drChargeId
     * @param int $orderId
     * @param string $drOrderId
     * @param string $drSourceId
     * @param string $drSourceType
     * @param float $amount
     */
    public function saveCharge(
        string $drChargeId,
        int $orderId,
        string $drOrderId,
        string $drSourceId,
        string $drSourceType,
        float $amount
    ): void;
}
