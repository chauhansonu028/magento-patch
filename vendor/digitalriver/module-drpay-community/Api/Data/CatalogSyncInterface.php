<?php
/**
 * Catalog Sync Data Interface
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Api\Data;

/**
 * Catalog Sync data structure
 */
interface CatalogSyncInterface
{
    /**
     * Constants defined for keys of data array
     */
    public const ENTITY_ID = 'entity_id';
    public const PRODUCT_ID = 'product_id';
    public const STATUS = 'status';
    public const REQUEST_DATA = 'request_data';
    public const RESPONSE_DATA = 'response_data';
    public const ADDED_TO_QUEUE_AT = 'added_to_queue_at';
    public const SYNCED_TO_DR_AT = 'synced_to_dr_at';
    public const PRODUCT_SKU = 'product_sku';

    /**
     * Get ID
     *
     * @return mixed
     */
    public function getId();

    /**
     * Get product id
     *
     * @return string|null
     */
    public function getProductId(): ?string;

    /**
     * Get Status
     *
     * @return string|null
     */
    public function getStatus(): ?string;

    /**
     * Get request data
     *
     * @return string|null
     */
    public function getRequestData(): ?string;

    /**
     * Get response data
     *
     * @return string|null
     */
    public function getResponseData(): ?string;

    /**
     * Get Added to Queue date
     *
     * @return string|null
     */
    public function getAddedToQueueAt(): ?string;

    /**
     * Get Synced to Digital river date
     *
     * @return string|null
     */
    public function getSyncedToDrAt(): ?string;

    /**
     * Get Product SKU
     *
     * @return string|null
     */
    public function getProductSku(): ?string;

    /**
     * Set ID
     *
     * @param mixed $id
     *
     * @return CatalogSyncInterface
     */
    public function setId($id): CatalogSyncInterface;

    /**
     * Set Product Id
     *
     * @param int|null $productId
     *
     * @return CatalogSyncInterface
     */
    public function setProductId(?int $productId): CatalogSyncInterface;

    /**
     * Set Status
     *
     * @param string|null $status
     *
     * @return CatalogSyncInterface
     */
    public function setStatus(?string $status): CatalogSyncInterface;

    /**
     * Set Request Data
     *
     * @param string|null $requestData
     *
     * @return CatalogSyncInterface
     */
    public function setRequestData(?string $requestData): CatalogSyncInterface;

    /**
     * Set Response Data
     *
     * @param string|null $responseData
     *
     * @return CatalogSyncInterface
     */
    public function setResponseData(?string $responseData): CatalogSyncInterface;

    /**
     * Set Added To Queue At
     *
     * @param string|null $addedToQueueAt
     *
     * @return CatalogSyncInterface
     */
    public function setAddedToQueueAt(?string $addedToQueueAt): CatalogSyncInterface;

    /**
     * Set Synced To Digital River
     *
     * @param string|null $syncedToDrAt
     *
     * @return CatalogSyncInterface
     */
    public function setSyncedToDrAt(?string $syncedToDrAt): CatalogSyncInterface;

    /**
     * Set Product SKU
     *
     * @param string|null $sku
     *
     * @return CatalogSyncInterface
     */
    public function setProductSku(?string $sku): CatalogSyncInterface;
}
