<?php

declare(strict_types=1);

namespace Digitalriver\DrPay\Plugin\Sales\Order\Creditmemo;

use Digitalriver\DrPay\Api\OfflineRefundRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoExtensionFactory;
use Magento\Sales\Api\Data\CreditmemoExtensionInterface;

class AddOfflineRefundExtensionAttribute
{
    /**
     * @var CreditmemoExtensionFactory
     */
    private $extensionFactory;

    /**
     * @var OfflineRefundRepositoryInterface
     */
    private $offlineRefundRepository;

    /**
     * @param CreditmemoExtensionFactory $extensionFactory
     * @param OfflineRefundRepositoryInterface $offlineRefundRepository
     */
    public function __construct(
        CreditmemoExtensionFactory $extensionFactory,
        OfflineRefundRepositoryInterface $offlineRefundRepository
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->offlineRefundRepository = $offlineRefundRepository;
    }

    /**
     * Lazy load for DrOffline attribute.
     *
     * @param CreditmemoInterface $creditmemo
     * @param CreditmemoExtensionInterface|null $extensionAttributes
     *
     * @return CreditmemoExtensionInterface
     */
    public function afterGetExtensionAttributes(
        CreditmemoInterface $creditmemo,
        CreditmemoExtensionInterface $extensionAttributes = null
    ) {
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->extensionFactory->create();
            $creditmemo->setExtensionAttributes($extensionAttributes);
        }

        if ($creditmemo->getEntityId() && $extensionAttributes->getDrOfflineRefund() === null) {
            try {
                $extensionAttributes->setDrOfflineRefund(
                    $this->offlineRefundRepository->getByCreditmemoId((int) $creditmemo->getEntityId())
                );
            } catch (NoSuchEntityException $e) {
                // Do nothing, the value will remain null
            }
        }

        return $extensionAttributes;
    }
}
