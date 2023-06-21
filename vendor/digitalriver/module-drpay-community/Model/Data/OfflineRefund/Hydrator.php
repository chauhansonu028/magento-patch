<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\Data\OfflineRefund;

use Digitalriver\DrPay\Api\Data\OfflineRefundInterface;
use Laminas\Hydrator\HydratorInterface;
use Magento\Framework\Exception\InputException;

class Hydrator implements HydratorInterface
{
    private const REQUIRED_FIELD_ERR_MSG = 'The field "%1" is required, but missing';
    private const REQUIRED_FIELDS = [
        OfflineRefundInterface::FIELD_DR_REFUND_ID,
        OfflineRefundInterface::FIELD_CREDIT_MEMO_ID,
        OfflineRefundInterface::FIELD_STATUS,
    ];

    /**
     * Extract values from an object
     *
     * @param OfflineRefundInterface $object
     * @return array
     * @throws InputException
     */
    public function extract($object): array
    {
        if (!$object instanceof OfflineRefundInterface) {
            throw new InputException(__('Expected %1, got %2', OfflineRefundInterface::class, get_class($object)));
        }
        return [
            OfflineRefundInterface::FIELD_DR_REFUND_ID => $object->getDrRefundId(),
            OfflineRefundInterface::FIELD_CREDIT_MEMO_ID => $object->getCreditmemoId(),
            OfflineRefundInterface::FIELD_STATUS => $object->getStatus(),
            OfflineRefundInterface::FIELD_REFUND_TOKEN => $object->getRefundToken(),
            OfflineRefundInterface::FIELD_REFUND_TOKEN_EXPIRY => $object->getRefundTokenExpiry(),
        ];
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param array $data
     * @param OfflineRefundInterface $object
     * @return OfflineRefundInterface
     * @throws InputException
     */
    public function hydrate(array $data, $object)
    {
        if (!$object instanceof OfflineRefundInterface) {
            throw new InputException(__('Expected %1, got %2', OfflineRefundInterface::class, get_class($object)));
        }
        foreach (self::REQUIRED_FIELDS as $requiredField) {
            if (!isset($data[$requiredField])) {
                throw new InputException(__('The field "%1" is required, but missing', $requiredField));
            }
        }
        $object->setDrRefundId((string)$data[OfflineRefundInterface::FIELD_DR_REFUND_ID]);
        $object->setCreditmemoId((int)$data[OfflineRefundInterface::FIELD_CREDIT_MEMO_ID]);
        $object->setStatus((int)$data[OfflineRefundInterface::FIELD_STATUS]);
        if (isset($data[OfflineRefundInterface::FIELD_REFUND_TOKEN])) {
            $object->setRefundToken((string)$data[OfflineRefundInterface::FIELD_REFUND_TOKEN]);
        } else {
            $object->setRefundToken(null);
        }
        if (isset($data[OfflineRefundInterface::FIELD_REFUND_TOKEN_EXPIRY])) {
            $object->setRefundTokenExpiry((string)$data[OfflineRefundInterface::FIELD_REFUND_TOKEN_EXPIRY]);
        } else {
            $object->setRefundTokenExpiry(null);
        }

        return $object;
    }
}
