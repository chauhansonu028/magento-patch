<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Observer;

use Digitalriver\DrPay\Api\OfflineRefundManagementInterface;
use Digitalriver\DrPay\Api\Data\OfflineRefundInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
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
     * @var Json
     */
    private $jsonSerializer;

    private const REFUND_COMPLETE_STATE = 'succeeded';

    /**
     * @param OfflineRefundManagementInterface $offlineRefundManagement
     */
    public function __construct(
        Json $jsonSerializer,
        OfflineRefundManagementInterface $offlineRefundManagement
    ) {
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

        $refundId = $refundData['id'] ?? null;
        if (isset($refundData['state'])
            && isset($refundData['state']) === 'succeeded') {
                $this->offlineRefundManagement->makeStatusSuccess((string)$refundId);
        }
    }
}
