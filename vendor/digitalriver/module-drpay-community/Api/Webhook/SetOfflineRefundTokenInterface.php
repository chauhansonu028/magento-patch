<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Api\Webhook;

use Digitalriver\DrPay\Api\Data\OfflineRefundInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Interface UpdateOfflineRefundTokenInterface
 * Updates offline refund token with Digital River data
 */
interface SetOfflineRefundTokenInterface
{
    public const FIELD_REFUND_ID = 'id';
    public const FIELD_TOKEN = 'token';
    public const FIELD_TOKEN_EXPIRATION = 'expiresTime';
    public const NODE_METADATA = 'metadata';
    public const FIELD_MEMO_ID = 'magentoCreditmemoId';
    public const NODE_TOKEN_INFORMATION = 'tokenInformation';

    /**
     * @param string $drRefundId
     * @param int $creditMemoId
     * @param string $token
     * @param string $tokenExpiration
     *
     * @return OfflineRefundInterface
     * @throws CouldNotSaveException
     */
    public function execute(
        string $drRefundId,
        int $creditMemoId,
        string $token,
        string $tokenExpiration
    ): OfflineRefundInterface;
}
