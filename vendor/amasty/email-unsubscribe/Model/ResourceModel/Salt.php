<?php

declare(strict_types=1);

namespace Amasty\EmailUnsubscribe\Model\ResourceModel;

class Salt extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const TABLE_NAME = 'flag';
    public const TABLE_ALIAS = 'flag';

    public const ID = 'flag_id';
    public const FLAG_CODE = 'flag_code';
    public const FLAG_CODE_VALUE = 'amasty_emailunsubscribe_salt';
    public const FLAG_DATA = 'flag_data';

    public function _construct()
    {
        $this->_init(self::TABLE_NAME, self::ID);
    }

    public function insert(string $salt): string
    {
        $table = $this->getTable(self::TABLE_NAME);
        $this->getConnection()->insert($table, [self::FLAG_CODE => self::FLAG_CODE_VALUE, self::FLAG_DATA => $salt]);

        return $this->getConnection()->lastInsertId($table);
    }

    public function getSalt(): string
    {
        $select = $this->getConnection()->select()
            ->from(
                [self::TABLE_ALIAS => $this->getTable(self::TABLE_NAME)],
                [self::FLAG_DATA]
            )
            ->where(sprintf('%s.%s = \'%s\'', self::TABLE_ALIAS, self::FLAG_CODE, self::FLAG_CODE_VALUE));

        return $this->getConnection()->fetchOne($select) ?: '';
    }
}
