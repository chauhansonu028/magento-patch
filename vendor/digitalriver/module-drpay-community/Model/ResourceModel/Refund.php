<?php
/**
 * Refund Resource model
 *
 * @author
 *
 * @category Digitalriver
 * @package Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Refund sync Resource model.
 */
class Refund extends AbstractDb
{
    /**
     * Define Table name for store sync data
     */
    private const DIGITAL_RIVER_REFUND = 'dr_refund';

    /**
     * Table Primary Key
     */
    const DR_REFUND_ID = 'dr_refund_id';

    /**
     * We're not using an autoincrement primary key for refunds
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Initialize strategic resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(self::DIGITAL_RIVER_REFUND, self::DR_REFUND_ID);
    }

    /**
     * Get Table name
     *
     * @return string
     */
    public function getTableName(): string
    {
        $connection = $this->getConnection();
        return $connection->getTableName(self::DIGITAL_RIVER_REFUND);
    }

    /**
     * Get Custom Table name
     *
     * @param string $tableName
     * @return string
     */
    public function getCustomTable(string $tableName): string
    {
        $connection = $this->getConnection();
        return $connection->getTableName($tableName);
    }
}
