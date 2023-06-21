<?php
/**
 * Provides countries information.
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Eccn Resource
 */
class EccnCode extends AbstractDb
{
    /**
     * Table for saving ECCN code data
     */
    private const DIGITAL_RIVER_ECCN_CODE = 'dr_eccn_code';

    /**
     * Table Primary Key
     */
    const CLASSIFICATION_CODE = 'classification_code';

    /**
     * Initialize Resource
     *
     * @return void
     */
    public function _construct(): void
    {
        $this->_init(self::DIGITAL_RIVER_ECCN_CODE, self::CLASSIFICATION_CODE);
    }
}
