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
 * Class CountryOfOrigin Resource
 */
class CountryOfOrigin extends AbstractDb
{
    /**
     * Table for saving countries data
     */
    private const DIGITAL_RIVER_COUNTRY_OF_ORIGIN = 'dr_country_of_origin';

    /**
     * Table Primary Key
     */
    const ENTITY_ID = 'entity_id';

    /**
     * Initialize Resource
     *
     * @return void
     */
    public function _construct(): void
    {
        $this->_init(self::DIGITAL_RIVER_COUNTRY_OF_ORIGIN, self::ENTITY_ID);
    }
}
