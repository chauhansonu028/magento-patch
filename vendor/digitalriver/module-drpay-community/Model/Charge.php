<?php declare(strict_types=1);
/**
 *  Charge model
 *
 * @summary Provides model for charges
 * @author Vujadin Scepanovic <vujadin.scepanovic@rs.ey.com>
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Api\Data\ChargeInterface;
use Digitalriver\DrPay\Model\ResourceModel\Charge as ChargeResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Data model of Charge Interface.
 */
class Charge extends AbstractModel implements ChargeInterface
{
    /**
     * Initialize Validate model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(ChargeResource::class);
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
     * @return ChargeInterface
     */
    public function setId($id): ChargeInterface
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get charge id
     *
     * @return string
     */
    public function getDrChargeId(): string
    {
        return $this->getData(self::DR_CHARGE_ID);
    }

    /**
     * Get Order Id
     *
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Get DR Order ID
     *
     * @return string
     */
    public function getDrOrderId(): string
    {
        return $this->getData(self::DR_ORDER_ID);
    }

    /**
     * Get DR Source ID
     *
     * @return string
     */
    public function getDrSourceId(): string
    {
        return $this->getData(self::DR_SOURCE_ID);
    }

    /**
     * Get DR Source Type
     *
     * @return string|null
     */
    public function getDrSourceType(): string
    {
        return $this->getData(self::DR_SOURCE_TYPE);
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount(): float
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * Get Retrived at
     *
     * @return string
     */
    public function getRetrievedAt(): string
    {
        return $this->getData(self::RETRIEVED_AT);
    }

    /**
     * Set DR Charge Id
     *
     * @param string $drChargeId
     *
     * @return ChargeInterface
     */
    public function setDrChargeId(string $drChargeId): ChargeInterface
    {
        return $this->setData(self::DR_CHARGE_ID, $drChargeId);
    }

    /**
     * Set Order ID
     *
     * @param int $orderId
     *
     * @return ChargeInterface
     */
    public function setStatus(int $orderId): ChargeInterface
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Set DR Order ID
     *
     * @param string $drOrderId
     *
     * @return ChargeInterface
     */
    public function setDrOrderId(string $drOrderId): ChargeInterface
    {
        return $this->setData(self::DR_ORDER_ID, $drOrderId);
    }

    /**
     * Set DR Source ID
     *
     * @param string $drSourceId
     *
     * @return ChargeInterface
     */
    public function setResponseData(string $drSourceId): ChargeInterface
    {
        return $this->setData(self::DR_SOURCE_ID, $drSourceId);
    }

    /**
     * Set DR Source ID
     *
     * @param string $drSourceId
     *
     * @return ChargeInterface
     */
    public function setDrSourceId(string $drSourceId): ChargeInterface
    {
        return $this->setData(self::DR_SOURCE_ID, $drSourceId);
    }

    /**
     * Set DR Source Type
     *
     * @param string $drSourceType
     *
     * @return ChargeInterface
     */
    public function setDrSourceType(string $drSourceType): ChargeInterface
    {
        return $this->setData(self::DR_SOURCE_TYPE, $drSourceType);
    }

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return ChargeInterface
     */
    public function setAmount(float $amount): ChargeInterface
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * Set Retrieved At
     *
     * @param string $retrievedAt
     *
     * @return ChargeInterface
     */
    public function setRetrievedAt(string $retrievedAt): ChargeInterface
    {
        return $this->setData(self::RETRIEVED_AT, $retrievedAt);
    }

    /**
     * Sets order id
     *
     * @param int $orderId
     * @return ChargeInterface
     */
    public function setOrderId(int $orderId): ChargeInterface
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }
}
