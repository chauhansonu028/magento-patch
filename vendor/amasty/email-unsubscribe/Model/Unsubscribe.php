<?php

declare(strict_types=1);

namespace Amasty\EmailUnsubscribe\Model;

use Amasty\EmailUnsubscribe\Model\ResourceModel\UnsubscribeType;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

class Unsubscribe
{
    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var UnsubscribeType
     */
    private $unsubscribeType;

    /**
     * @var array
     */
    private $types;

    /**
     * @var ResourceModel\Unsubscribe
     */
    private $unsubscribe;

    public function __construct(
        MessageManagerInterface $messageManager,
        UnsubscribeType $unsubscribeType,
        ResourceModel\Unsubscribe $unsubscribe,
        array $types = []
    ) {
        $this->messageManager = $messageManager;
        $this->unsubscribeType = $unsubscribeType;
        $this->types = $types;
        $this->unsubscribe = $unsubscribe;
    }

    public function execute(string $type, string $email, int $entityId): string
    {
        if (isset($this->types[$type])) {
            $typeData = $this->types[$type];
            $typeId = $this->getTypeId($type);
            try {
                $this->unsubscribe->execute($typeId, $email, $entityId);
                $this->messageManager->addSuccessMessage(__($typeData['successMessage']));
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __($typeData['exceptionMessage']));
            }
        }

        return $typeData['redirectPath'] ?? '/';
    }

    private function getTypeId(string $type): int
    {
        $typeId = $this->unsubscribeType->getTypeId($type);
        if (!$typeId) {
            $typeId = $this->unsubscribeType->insert($type);
        }

        return $typeId;
    }
}
