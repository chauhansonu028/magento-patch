<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Plugin\Sales\Model\Service\CreditmemoService;

use Digitalriver\DrPay\Api\RefundRegistryInterface;
use Digitalriver\DrPay\Helper\Drapi;
use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateOfflineRefund
 *
 * Adds credit memo ID to offline refund record
 */
class UpdateOfflineRefund
{
    /**
     * @var RefundRegistryInterface
     */
    private $refundRegistry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Drapi
     */
    private $client;

    /**
     * UpdateOfflineRefund constructor.
     *
     * @param RefundRegistryInterface $refundRegistry
     * @param Drapi $client
     * @param LoggerInterface $logger
     */
    public function __construct(
        RefundRegistryInterface $refundRegistry,
        Drapi $client,
        LoggerInterface $logger
    ) {
        $this->refundRegistry = $refundRegistry;
        $this->logger = $logger;
        $this->client = $client;
    }

    /**
     * @param CreditmemoManagementInterface $subject
     * @param CreditmemoInterface $result
     *
     * @return CreditmemoInterface
     */
    public function afterRefund(
        CreditmemoManagementInterface $subject,
        CreditmemoInterface $result
    ): CreditmemoInterface {
        $drRefundId = $this->refundRegistry->getCurrentDrRefundId();
        if ($drRefundId !== null) {
            $apiResult = $this->client->setRefundCreditMemoId($drRefundId, (int)$result->getEntityId(), (int)$result->getStoreId());
            if ($apiResult['success'] !== true) {
                $this->logger->error('Refund update was not completed.', ['apiResponse' => $apiResult]);
            }
        }
        return $result;
    }
}
