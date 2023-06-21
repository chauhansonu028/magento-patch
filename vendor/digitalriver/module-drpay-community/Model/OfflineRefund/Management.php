<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\OfflineRefund;

use Digitalriver\DrPay\Api\Data\OfflineRefundInterfaceFactory;
use Digitalriver\DrPay\Api\OfflineRefundManagementInterface;
use Digitalriver\DrPay\Api\OfflineRefundRepositoryInterface;
use Digitalriver\DrPay\Api\Data\OfflineRefundInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Update offline refund management
 */
class Management implements OfflineRefundManagementInterface
{
    /**
     * @var OfflineRefundRepositoryInterface
     */
    private $offlineRefundRepository;

    /**
     * @var OfflineRefundInterfaceFactory
     */
    private $refundFactory;

    /**
     * @param OfflineRefundRepositoryInterface $offlineRefundRepository
     * @param OfflineRefundInterfaceFactory $refundFactory
     */
    public function __construct(
        OfflineRefundRepositoryInterface $offlineRefundRepository,
        OfflineRefundInterfaceFactory $refundFactory
    ) {
        $this->offlineRefundRepository = $offlineRefundRepository;
        $this->refundFactory = $refundFactory;
    }

    /**
     * PUT Update offline refund status to "success"
     *
     * @param string $drRefundId
     *
     * @return bool
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function makeStatusSuccess(string $drRefundId): bool
    {
        $offlineRefund = $this->offlineRefundRepository->get($drRefundId);
        $offlineRefund->setStatus(OfflineRefundInterface::STATUS_SUCCESS);
        $this->offlineRefundRepository->save($offlineRefund);

        return true;
    }

    /**
     * PUT Update offline refund status to "failure"
     *
     * @param string $drRefundId
     *
     * @return bool
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function makeStatusFailure(string $drRefundId): bool
    {
        $offlineRefund = $this->offlineRefundRepository->get($drRefundId);
        $offlineRefund->setStatus(OfflineRefundInterface::STATUS_FAIL);
        $this->offlineRefundRepository->save($offlineRefund);

        return true;
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
    public function setRefundToken(
        string $drRefundId,
        int $creditMemoId,
        string $token,
        string $tokenExpiration
    ): OfflineRefundInterface {
        $offlineRefund;
        $isRefundExist = $this->offlineRefundRepository->checkExistence($drRefundId);
        if ($isRefundExist) {
            $offlineRefund = $this->offlineRefundRepository->get($drRefundId);
            $offlineRefund->setStatus(OfflineRefundInterface::STATUS_PENDING_INFORMATION);
            $offlineRefund->setRefundToken($token);
            $offlineRefund->setRefundTokenExpiry($tokenExpiration);
            $this->offlineRefundRepository->save($offlineRefund);
        } else {
            $this->createRefund(
                $drRefundId,
                $creditMemoId,
                OfflineRefundInterface::STATUS_PENDING_INFORMATION
            );
            $offlineRefund = $this->offlineRefundRepository->get($drRefundId);
            $offlineRefund->setRefundToken($token);
            $offlineRefund->setRefundTokenExpiry($tokenExpiration);
            $this->offlineRefundRepository->save($offlineRefund);
        }
        return $offlineRefund;
    }

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
    ): OfflineRefundInterface {
        $offlineRefund = $this->refundFactory->create();
        $offlineRefund->isObjectNew(true);
        $offlineRefund->setDrRefundId($drRefundId);
        $offlineRefund->setCreditmemoId($creditMemoId);
        $offlineRefund->setStatus($refundStatus);
        $this->offlineRefundRepository->save($offlineRefund);

        return $offlineRefund;
    }
}
