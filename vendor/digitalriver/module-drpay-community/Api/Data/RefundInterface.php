<?php
/**
 * Refund Interface
 *
 * @summary
 * Represents an aggregated info on DR Refund
 * @author
 *
 * @category Digitalriver
 * @package Digitalriver_DrPay
 */

declare(strict_types=1);

namespace Digitalriver\DrPay\Api\Data;

/**
 * Catalog Sync data structure
 */
interface RefundInterface
{
    /**
     * Constants defined for keys of data array
     */

    public const DR_REFUND_ID = 'dr_refund_id';
    public const AMOUNT = 'amount';

    /**
     * Get DR Refund ID
     *
     * @return string
     */
    public function getId(): ?string;

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount(): float;

    /**
     * Set DR Refund Id
     *
     * @param string $drRefundId
     *
     * @return RefundInterface
     */
    public function setId(string $drRefundId): RefundInterface;

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return RefundInterface
     */
    public function setAmount(float $amount): RefundInterface;
}
