<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Observer;

use Digitalriver\DrPay\Api\OfflineRefundManagementInterface;
use Digitalriver\DrPay\Api\Webhook\SetOfflineRefundTokenInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Create offline refund entity on dr_create_refund_after
 */
class OnRefundPendingInformatoin implements ObserverInterface
{
    /**
     * @var OfflineRefundManagementInterface
     */
    private $offlineRefundManagement;

    private const REFUND_PENDING_STATE = 'pending_information';
    private const FIELD_STATE = 'state';

    /**
     * @param OfflineRefundManagementInterface $offlineRefundManagement
     */
    public function __construct(
        OfflineRefundManagementInterface $offlineRefundManagement
    ) {
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

        $metaData = $refundData[SetOfflineRefundTokenInterface::NODE_METADATA] ?? [];
        if ($refundData[self::FIELD_STATE] === self::REFUND_PENDING_STATE
            && isset($refundData[SetOfflineRefundTokenInterface::FIELD_REFUND_ID])
            && isset($metaData[SetOfflineRefundTokenInterface::FIELD_MEMO_ID])) {
            $tokenInfo = $refundData[SetOfflineRefundTokenInterface::NODE_TOKEN_INFORMATION] ?? [];
            $this->offlineRefundManagement->setRefundToken(
                (string)$refundData[SetOfflineRefundTokenInterface::FIELD_REFUND_ID],
                (int)$metaData[SetOfflineRefundTokenInterface::FIELD_MEMO_ID],
                $tokenInfo[SetOfflineRefundTokenInterface::FIELD_TOKEN] ?? '',
                $tokenInfo[SetOfflineRefundTokenInterface::FIELD_TOKEN_EXPIRATION] ?? ''
            );
        }
    }
}
