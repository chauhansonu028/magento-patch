<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\OfflineRefund;

use Digitalriver\DrPay\Api\RefundRegistryInterface;

/**
 * Class Registry
 *
 * Keeps current DR Refund ID during refund process.
 */
class Registry implements RefundRegistryInterface
{
    /**
     * @var string|null
     */
    private $currentDrRefundId = null;

    /**
     * @param string $drRefundId
     */
    public function setCurrentDrRefundId(string $drRefundId): void
    {
        $this->currentDrRefundId = $drRefundId;
    }

    /**
     * @return string|null
     */
    public function getCurrentDrRefundId(): ?string
    {
        return $this->currentDrRefundId;
    }
}
