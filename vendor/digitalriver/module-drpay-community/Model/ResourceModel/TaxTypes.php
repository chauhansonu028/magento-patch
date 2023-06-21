<?php
/**
 * DR Tax Types Resource Model
 *
 * Provides Tax Groups/Types codes.
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * DR Custom Tax Types Resource Model
 * Class TaxTypes
 */
class TaxTypes extends AbstractDb
{

    const DR_TAX_TABLE = 'dr_tax_table';

    const DR_TAX_TABLE_ID_FIELD_NAME = 'entity_id';

    /**
     * Initialize connection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(self::DR_TAX_TABLE, self::DR_TAX_TABLE_ID_FIELD_NAME);
    }
}
