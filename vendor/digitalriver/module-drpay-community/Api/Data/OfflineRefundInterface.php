<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Api\Data;

/**
 * Offline Order data model interface
 */
interface OfflineRefundInterface
{
    /**
     * Constants defined for keys of data array
     */
    public const FIELD_DR_REFUND_ID = 'dr_refund_id';
    public const FIELD_CREDIT_MEMO_ID = 'credit_memo_id';
    public const FIELD_STATUS = 'status';
    public const FIELD_REFUND_TOKEN = 'refund_token';
    public const FIELD_REFUND_TOKEN_EXPIRY = 'refund_token_expiration';

    /**
     * Possible statuses for status field.
     */
    public const STATUS_SUCCESS = 1;
    public const STATUS_FAIL = 2;
    public const STATUS_PENDING = 0;
    public const STATUS_PENDING_INFORMATION = 3;
    public const STATUS_STATUS_EXPIRED = 4;

    /**
     * Get DR Refund ID
     *
     * @return string
     */
    public function getDrRefundId(): ?string;

    /**
     * Get creditmemo ID
     *
     * @return int|null
     */
    public function getCreditmemoId(): ?int;

    /**
     * Get status
     *
     * @return int|null
     */
    public function getStatus(): ?int;

    /**
     * Get refund token
     *
     * @return string|null
     */
    public function getRefundToken(): ?string;

    /**
     * Get refund token expiration
     *
     * @return string|null
     */
    public function getRefundTokenExpiry(): ?string;

    /**
     * Set DR Refund Id
     *
     * @param string $drRefundId
     *
     * @return OfflineRefundInterface
     */
    public function setDrRefundId(string $drRefundId): self;

    /**
     * Get creditmemo ID
     *
     * @param int $creditmemoId
     *
     * @return OfflineRefundInterface
     */
    public function setCreditmemoId(int $creditmemoId): self;

    /**
     * Get status
     *
     * @param int $status
     * @return OfflineRefundInterface
     */
    public function setStatus(int $status): self;

    /**
     * Get refund token
     *
     * @param string|null $token
     * @return OfflineRefundInterface
     */
    public function setRefundToken(?string $token): self;

    /**
     * Get refund token expiration
     *
     * @param string|null $expiry
     * @return OfflineRefundInterface
     */
    public function setRefundTokenExpiry(?string $expiry): self;
}
