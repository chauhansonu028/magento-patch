<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Api;

/**
 * Interface RefundRegistryInterface
 *
 * Keeps current DR Refund ID during refund process.
 *
 * @api
 */
interface RefundRegistryInterface
{
    /**
     * @param string $drRefundId
     * @return void
     */
    public function setCurrentDrRefundId(string $drRefundId): void;

    /**
     * @return string|null
     */
    public function getCurrentDrRefundId(): ?string;
}
