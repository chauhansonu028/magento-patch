<?php
/**
 * DR Tax Groups/Types Model
 *
 * Provides Tax Groups/Types attributes information.
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * DR Custom Tax Types Model
 * Class TaxTypes
 */
class TaxTypes extends AbstractModel
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(\Digitalriver\DrPay\Model\ResourceModel\TaxTypes::class);
    }
}
