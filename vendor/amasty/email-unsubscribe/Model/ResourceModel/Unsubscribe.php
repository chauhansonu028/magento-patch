<?php

declare(strict_types=1);

namespace Amasty\EmailUnsubscribe\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;

class Unsubscribe extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const TABLE_NAME = 'amasty_emailunsubscribe_relation';
    public const TABLE_ALIAS = 'ubsucribe_relation';

    public const TYPE_ID = 'type_id';
    public const EMAIL = 'email';
    public const ENTITY_ID = 'entity_id';

    /**
     * @var UnsubscribeType
     */
    private $unsubscribeType;

    public function __construct(
        Context $context,
        UnsubscribeType $unsubscribeType,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->unsubscribeType = $unsubscribeType;
    }

    public function _construct()
    {
        $this->_init(self::TABLE_NAME, self::TYPE_ID);
    }

    public function execute(int $typeId, string $email, int $entityId): int
    {
        return $this->getConnection()->insertOnDuplicate(
            $this->getTable(self::TABLE_NAME),
            [
                self::TYPE_ID => $typeId,
                self::EMAIL => $email,
                self::ENTITY_ID => $entityId
            ]
        );
    }
}
