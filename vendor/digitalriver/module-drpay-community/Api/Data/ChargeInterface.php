<?php
/**
 * Charge Interface
 *
 * @summary
 * Represents an aggregated info on DR Charge
 * @author Vujadin Scepanovic <vujadin.scepanovic@rs.ey.com>
 *
 * @category Digitalriver
 * @package Digitalriver_DrPay
 */

declare(strict_types=1);

namespace Digitalriver\DrPay\Api\Data;

/**
 * Catalog Sync data structure
 */
interface ChargeInterface
{
    /**
     * Constants defined for keys of data array
     */

    public const ENTITY_ID = 'entity_id';
    public const DR_CHARGE_ID = 'dr_charge_id';
    public const ORDER_ID = 'order_id';
    public const DR_ORDER_ID = 'dr_order_id';
    public const DR_SOURCE_ID = 'dr_source_id';
    public const DR_SOURCE_TYPE = 'dr_source_type';
    public const AMOUNT = 'amount';
    public const RETRIEVED_AT = 'retrieved_at';

    /**
     * Get ID
     *
     * @return mixed
     */
    public function getId();

    /**
     * Get DR Charge ID
     *
     * @return string
     */
    public function getDrChargeId(): string;

    /**
     * Get Order ID
     *
     * @return int
     */
    public function getOrderId(): int;

    /**
     * Get DR Order ID
     *
     * @return string
     */
    public function getDrOrderId(): string;

    /**
     * Get DR Source ID
     *
     * @return string
     */
    public function getDrSourceId(): string;

    /**
     * Get DR Source Type
     *
     * @return string
     */
    public function getDrSourceType(): string;

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount(): float;

    /**
     * Get retrieved at
     *
     * @return string
     */
    public function getRetrievedAt(): string;

    /**
     * Set ID
     *
     * @param mixed $id
     *
     * @return ChargeInterface
     */
    public function setId($id): ChargeInterface;

    /**
     * Set DR Charge Id
     *
     * @param string $drChargeId
     *
     * @return ChargeInterface
     */
    public function setDrChargeId(string $drChargeId): ChargeInterface;

    /**
     * Set Status
     *
     * @param int $orderId
     *
     * @return ChargeInterface
     */
    public function setOrderId(int $orderId): ChargeInterface;

    /**
     * Set Request Data
     *
     * @param string $drOrderId
     *
     * @return ChargeInterface
     */
    public function setDrOrderId(string $drOrderId): ChargeInterface;

    /**
     * Set Response Data
     *
     * @param string $drSourceId
     *
     * @return ChargeInterface
     */
    public function setDrSourceId(string $drSourceId): ChargeInterface;

    /**
     * Set DR Source Type
     *
     * @param string $drSourceType
     *
     * @return ChargeInterface
     */
    public function setDrSourceType(string $drSourceType): ChargeInterface;

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return ChargeInterface
     */
    public function setAmount(float $amount): ChargeInterface;

    /**
     * Set amount
     *
     * @param string $timestamp
     *
     * @return ChargeInterface
     */
    public function setRetrievedAt(string $timestamp): ChargeInterface;
}
