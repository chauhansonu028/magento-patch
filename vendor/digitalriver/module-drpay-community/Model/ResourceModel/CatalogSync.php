<?php
/**
 * Catalog Sync Resource Model
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Catalog sync Resource model.
 */
class CatalogSync extends AbstractDb
{
    /**
     * Define Table name for store sync data
     */
    private const DIGITAL_RIVER_CATALOG_SYNC_QUEUE = 'dr_catalog_sync_queue';

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
        $this->_init(self::DIGITAL_RIVER_CATALOG_SYNC_QUEUE, self::ENTITY_ID);
    }

    /**
     * Get Table name
     *
     * @return string
     */
    public function getTableName(): string
    {
        $connection = $this->getConnection();
        return $connection->getTableName(self::DIGITAL_RIVER_CATALOG_SYNC_QUEUE);
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
     * Get Catalog Sync Id By Product Id
     *
     * @param int $productId
     *
     * @return int|null
     */
    public function getCatalogSyncIdByProductId(int $productId): int
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTableName(), 'entity_id')
            ->where('product_id = :product_id');

        $bind = [
            ':product_id' => (int)$productId
        ];
        return (int)$connection->fetchOne($select, $bind);
    }
}
