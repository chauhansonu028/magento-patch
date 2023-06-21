<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Api;

use Digitalriver\DrPay\Api\Data\OfflineRefundInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Update offline refund management interface
 */
interface OfflineRefundManagementInterface
{
    /**
     * PUT Update offline refund status to "success"
     *
     * @param string $drRefundId
     *
     * @return bool
     */
    public function makeStatusSuccess(string $drRefundId): bool;

    /**
     * PUT Update offline refund status to "failure"
     *
     * @param string $drRefundId
     *
     * @return bool
     */
    public function makeStatusFailure(string $drRefundId): bool;

    /**
     * @param string $drRefundId
     * @param int $creditMemoId
     * @param string $token
     * @param string $tokenExpiration
     *
     * @return OfflineRefundInterface
     * @throws CouldNotSaveException
     */
    public function setRefundToken(
        string $drRefundId,
        int $creditMemoId,
        string $token,
        string $tokenExpiration
    ): OfflineRefundInterface;

    /**
     * @param string $drRefundId
     * @param int $creditMemoId
     * @param int $refundStatus
     *
     * @return OfflineRefundInterface
     * @throws CouldNotSaveException
     */
    public function createRefund(
        string $drRefundId,
        int $creditMemoId,
        int $refundStatus
    ): OfflineRefundInterface;
}
