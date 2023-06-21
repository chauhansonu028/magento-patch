<?php

declare(strict_types=1);

namespace Digitalriver\DrPay\Model\Data;

use Digitalriver\DrPay\Api\Data\OfflineRefundInterface;
use Digitalriver\DrPay\Model\ResourceModel\OfflineRefund as OfflineRefundResource;
use Magento\Framework\Model\AbstractModel;

/**
 * OfflineRefund data model
 */
class OfflineRefund extends AbstractModel implements OfflineRefundInterface
{
    /**
     * Magento DB model initialization
     */
    protected function _construct() // NOSONAR
    {
        $this->_init(OfflineRefundResource::class);
    }

    /**
     * Get DR Refund ID
     *
     * @return string
     */
    public function getDrRefundId(): ?string
    {
        $value = $this->getData(self::FIELD_DR_REFUND_ID);

        return $value === null ? null : (string) $value;
    }

    /**
     * Get creditmemo ID
     *
     * @return int|null
     */
    public function getCreditmemoId(): ?int
    {
        $value = $this->getData(self::FIELD_CREDIT_MEMO_ID);

        return $value === null ? null : (int) $value;
    }

    /**
     * Get status
     *
     * @return int|null
     */
    public function getStatus(): ?int
    {
        $value = $this->getData(self::FIELD_STATUS);

        return $value === null ? null : (int) $value;
    }

    /**
     * Get refund token
     *
     * @return string|null
     */
    public function getRefundToken(): ?string
    {
        $value = $this->getData(self::FIELD_REFUND_TOKEN);

        return $value === null ? null : (string) $value;
    }

    /**
     * Get refund token expiration
     *
     * @return string|null
     */
    public function getRefundTokenExpiry(): ?string
    {
        $value = $this->getData(self::FIELD_REFUND_TOKEN_EXPIRY);

        return $value === null ? null : (string) $value;
    }

    /**
     * Set DR Refund Id
     *
     * @param string $drRefundId
     *
     * @return OfflineRefundInterface
     */
    public function setDrRefundId(string $drRefundId): OfflineRefundInterface
    {
        $this->setData(self::FIELD_DR_REFUND_ID, $drRefundId);

        return $this;
    }

    /**
     * Set creditmemo ID
     *
     * @param int $creditmemoId
     *
     * @return OfflineRefundInterface
     */
    public function setCreditmemoId(int $creditmemoId): OfflineRefundInterface
    {
        $this->setData(self::FIELD_CREDIT_MEMO_ID, $creditmemoId);

        return $this;
    }

    /**
     * Set status
     *
     * @param int $status
     * @return OfflineRefundInterface
     */
    public function setStatus(int $status): OfflineRefundInterface
    {
        $this->setData(self::FIELD_STATUS, $status);

        return $this;
    }

    /**
     * Set refund token
     *
     * @param string|null $token
     * @return OfflineRefundInterface
     */
    public function setRefundToken(?string $token): OfflineRefundInterface
    {
        $this->setData(self::FIELD_REFUND_TOKEN, $token);

        return $this;
    }

    /**
     * Get refund token expiration
     *
     * @param string|null $expiry
     * @return OfflineRefundInterface
     */
    public function setRefundTokenExpiry(?string $expiry): OfflineRefundInterface
    {
        $this->setData(self::FIELD_REFUND_TOKEN_EXPIRY, $expiry);

        return $this;
    }
}
