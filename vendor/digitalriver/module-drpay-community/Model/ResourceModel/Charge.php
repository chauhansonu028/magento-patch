<?php
/**
 * Charge Resource model
 *
 * @author Vujadin Scepanovic <vujadin.scepanovic@rs.ey.com>
 *
 * @category Digitalriver
 * @package Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Catalog sync Resource model.
 */
class Charge extends AbstractDb
{
    /**
     * Define Table name for store sync data
     */
    private const DIGITAL_RIVER_CHARGE = 'dr_charge';

    /**
     * Table Primary Key
     */
    const ENTITY_ID = 'entity_id';

    /**
     * Initialize strategic resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(self::DIGITAL_RIVER_CHARGE, self::ENTITY_ID);
    }

    /**
     * Get Table name
     *
     * @return string
     */
    public function getTableName(): string
    {
        $connection = $this->getConnection();
        return $connection->getTableName(self::DIGITAL_RIVER_CHARGE);
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

    /**
     * Get Charge ID by DR Charge ID
     *
     * @param string $drChargeId
     * @return int
     */
    public function getChargeIdByDrChargeId(string $drChargeId): int
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTableName(), 'entity_id')
            ->where('dr_charge_id = :dr_charge_id');

        $bind = [
            ':dr_charge_id' => $drChargeId
        ];
        return (int)$connection->fetchOne($select, $bind);
    }
}
