<?php declare(strict_types=1);
/**
 * Catalog Sync Repository Interface
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Api;

use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Digitalriver\DrPay\Api\Data\CatalogSyncInterface;

/**
 * Digital River Catalog Sync CRUD interface.
 */
interface CatalogSyncRepositoryInterface
{
    /**
     * Save Catalog Sync Product Record.
     *
     * @param CatalogSyncInterface $catalogSync
     *
     * @return void
     * @throws LocalizedException
     */
    public function save(CatalogSyncInterface $catalogSync): void;

    /**
     * Retrieve Catalog Sync data by id.
     *
     * @param int $catalogSyncId
     *
     * @return CatalogSyncInterface
     * @throws LocalizedException
     */
    public function getById(int $catalogSyncId): CatalogSyncInterface;

    /**
     * Retrieve Catalog Sync matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return SearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;

    /**
     * Delete Catalog Sync.
     *
     * @param CatalogSyncInterface $catalogSync
     *
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(CatalogSyncInterface $catalogSync): bool;

    /**
     * Delete Catalog Sync by ID.
     *
     * @param int $syncId
     *
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $syncId): bool;
}
