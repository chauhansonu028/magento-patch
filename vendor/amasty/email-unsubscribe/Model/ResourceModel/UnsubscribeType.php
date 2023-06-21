<?php

declare(strict_types=1);

namespace Amasty\EmailUnsubscribe\Model\ResourceModel;

class UnsubscribeType extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const TABLE_NAME = 'amasty_emailunsubscribe_type';
    public const TABLE_ALIAS = 'unsubscribe_type';

    public const TYPE_ID = 'type_id';
    public const TYPE = 'type';

    public function _construct()
    {
        $this->_init(self::TABLE_NAME, self::TYPE_ID);
    }

    public function insert(string $type): int
    {
        $table = $this->getTable(self::TABLE_NAME);
        $this->getConnection()->insert($table, [self::TYPE => $type]);

        return (int) $this->getConnection()->lastInsertId($table);
    }

    public function getTypeId(string $type): int
    {
        $select = $this->getConnection()->select()
            ->from(
                [self::TABLE_ALIAS => $this->getTable(self::TABLE_NAME)],
                [self::TYPE_ID]
            )
            ->where(sprintf('%s.%s = %d', self::TABLE_ALIAS, self::TYPE, $type));

        return (int) $this->getConnection()->fetchOne($select);
    }
}
