<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Observer;

use Digitalriver\DrPay\Api\OfflineRefundManagementInterface;
use Digitalriver\DrPay\Api\Data\OfflineRefundInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Digitalriver\DrPay\Logger\Logger;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Create offline refund entity on dr_create_refund_after
 */
class OnRefundCreate implements ObserverInterface
{
    /**
     * @var OfflineRefundManagementInterface
     */
    private $offlineRefundManagement;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @param OfflineRefundManagementInterface $offlineRefundManagement
     */
    public function __construct(
        Logger $logger,
        Json $jsonSerializer,
        OfflineRefundManagementInterface $offlineRefundManagement
    ) {
        $this->_logger = $logger;
        $this->jsonSerializer = $jsonSerializer;
        $this->offlineRefundManagement = $offlineRefundManagement;
    }

    /**
     * Create offline refund entity after refund API call.
     *
     * @param Observer $observer
     *
     * @return void
     * @throws CouldNotSaveException
     * @see refund.pending_information
     */
    public function execute(Observer $observer)
    {
        /** @var array $refundData */
        $refundData = $observer->getEvent()->getData('refund_data');

        $creditMemoId = $refundData['magentoCreditmemoId'] ?? null;
        $refundId = $refundData['id'] ?? null;
        
        if ( isset($refundData['id'])
            && isset($refundData['metadata']['magentoCreditmemoId']) 
            && isset($refundData['state'])
        ) {
            $refundStatus;
            switch ($refundData['state']) {
                case 'pending':
                    $refundStatus = OfflineRefundInterface::STATUS_PENDING;
                    break;
                case 'succeeded':
                    $refundStatus = OfflineRefundInterface::STATUS_SUCCESS;
                    break;
                case 'failed':
                    $refundStatus = OfflineRefundInterface::STATUS_FAIL;
                    break;
                case 'pending_information':
                    $refundStatus = OfflineRefundInterface::STATUS_PENDING_INFORMATION;
                    break;
                case 'expired':
                    $refundStatus = OfflineRefundInterface::STATUS_EXPIRED;
                    break;
                default:
                    $refundStatus = OfflineRefundInterface::STATUS_EXPIRED;
                    $this->_logger->error("\n\nDR REFUND ".__FUNCTION__.": Event state not predefined: \n" . $this->jsonSerializer->serialize($refundData)."\n");
            }
            $creditMemoId = $refundData['metadata']['magentoCreditmemoId'];
            $refundId = $refundData['id'];
            $this->offlineRefundManagement->createRefund(
                (string)$refundId,
                (int)$creditMemoId,
                (int)$refundStatus
            );
        }
    }
}
