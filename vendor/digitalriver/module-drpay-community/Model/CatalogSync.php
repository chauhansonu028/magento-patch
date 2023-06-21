<?php declare(strict_types=1);
/**
 * Catalog Sync Model
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Api\Data\CatalogSyncInterface;
use Digitalriver\DrPay\Model\ResourceModel\CatalogSync as CatalogSyncResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Data model of CatalogSync Interface.
 */
class CatalogSync extends AbstractModel implements CatalogSyncInterface
{
    /**
     * Initialize Validate model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(CatalogSyncResource::class);
    }

    /**
     * Retrieve entity id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set ID
     *
     * @param mixed $id
     *
     * @return CatalogSyncInterface
     */
    public function setId($id): CatalogSyncInterface
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get product id
     *
     * @return string|null
     */
    public function getProductId(): ?string
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * Get Status
     *
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Get request data
     *
     * @return string|null
     */
    public function getRequestData(): ?string
    {
        return $this->getData(self::REQUEST_DATA);
    }

    /**
     * Get response data
     *
     * @return string|null
     */
    public function getResponseData(): ?string
    {
        return $this->getData(self::RESPONSE_DATA);
    }

    /**
     * Get Added to Queue date
     *
     * @return string|null
     */
    public function getAddedToQueueAt(): ?string
    {
        return $this->getData(self::ADDED_TO_QUEUE_AT);
    }

    /**
     * Get Synced to Digital river date
     *
     * @return string|null
     */
    public function getSyncedToDrAt(): ?string
    {
        return $this->getData(self::SYNCED_TO_DR_AT);
    }

    /**
     * Get Product SKU
     *
     * @return string|null
     */
    public function getProductSku(): ?string
    {
        return $this->getData(self::PRODUCT_SKU);
    }

    /**
     * Set Product Id
     *
     * @param int|null $productId
     *
     * @return CatalogSyncInterface
     */
    public function setProductId(?int $productId): CatalogSyncInterface
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Set Status
     *
     * @param string|null $status
     *
     * @return CatalogSyncInterface
     */
    public function setStatus(?string $status): CatalogSyncInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Set Request Data
     *
     * @param string|null $requestData
     *
     * @return CatalogSyncInterface
     */
    public function setRequestData(?string $requestData): CatalogSyncInterface
    {
        return $this->setData(self::REQUEST_DATA, $requestData);
    }

    /**
     * Set Response Data
     *
     * @param string|null $responseData
     *
     * @return CatalogSyncInterface
     */
    public function setResponseData(?string $responseData): CatalogSyncInterface
    {
        return $this->setData(self::RESPONSE_DATA, $responseData);
    }

    /**
     * Set Added To Queue At
     *
     * @param string|null $addedToQueueAt
     *
     * @return CatalogSyncInterface
     */
    public function setAddedToQueueAt(?string $addedToQueueAt): CatalogSyncInterface
    {
        return $this->setData(self::ADDED_TO_QUEUE_AT, $addedToQueueAt);
    }

    /**
     * Set Synced To Digital River
     *
     * @param string|null $syncedToDrAt
     *
     * @return CatalogSyncInterface
     */
    public function setSyncedToDrAt(?string $syncedToDrAt): CatalogSyncInterface
    {
        return $this->setData(self::SYNCED_TO_DR_AT, $syncedToDrAt);
    }

    /**
     * Set Product SKU
     *
     * @param string|null $sku
     *
     * @return CatalogSyncInterface
     */
    public function setProductSku(?string $sku): CatalogSyncInterface
    {
        return $this->setData(self::PRODUCT_SKU, $sku);
    }
}
