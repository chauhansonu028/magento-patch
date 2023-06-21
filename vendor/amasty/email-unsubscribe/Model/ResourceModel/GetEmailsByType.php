<?php

declare(strict_types=1);

namespace Amasty\EmailUnsubscribe\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;

class GetEmailsByType extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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

    public function execute(string $type, array $entity_ids): array
    {
        $condition = sprintf(
            '%s.%s = %s.%s',
            self::TABLE_ALIAS,
            self::TYPE_ID,
            UnsubscribeType::TABLE_ALIAS,
            UnsubscribeType::TYPE_ID
        );

        $select = $this->getConnection()->select()
            ->from(
                [self::TABLE_ALIAS => $this->getTable(self::TABLE_NAME)],
                [self::EMAIL]
            )
            ->joinLeft(
                [UnsubscribeType::TABLE_ALIAS => $this->getTable(UnsubscribeType::TABLE_NAME)],
                $condition
            )
            ->where(sprintf('%s.%s = \'%s\'', UnsubscribeType::TABLE_ALIAS, UnsubscribeType::TYPE, $type))
            ->where(sprintf('%s.%s IN (?)', self::TABLE_ALIAS, self::ENTITY_ID), $entity_ids);

        return $this->getConnection()->fetchCol($select) ?? [];
    }
}
