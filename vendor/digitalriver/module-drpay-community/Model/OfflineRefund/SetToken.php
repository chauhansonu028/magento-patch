<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\OfflineRefund;

use Digitalriver\DrPay\Api\Data\OfflineRefundInterface;
use Digitalriver\DrPay\Api\OfflineRefundManagementInterface;
use Digitalriver\DrPay\Api\Webhook\SetOfflineRefundTokenInterface;
use Magento\Framework\Exception\CouldNotSaveException;

class SetToken implements SetOfflineRefundTokenInterface
{
    /**
     * @var OfflineRefundManagementInterface
     */
    private $offlineRefundManagement;

    /**
     * UpdateOfflineRefundToken constructor.
     *
     * @param OfflineRefundManagementInterface $offlineRefundManagement
     */
    public function __construct(OfflineRefundManagementInterface $offlineRefundManagement)
    {
        $this->offlineRefundManagement = $offlineRefundManagement;
    }

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
    ): OfflineRefundInterface {
        return $this->offlineRefundManagement->setRefundToken($drRefundId, $creditMemoId, $token, $tokenExpiration);
    }
}
