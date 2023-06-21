<?php
/**
 * DR Tax Groups/Types Collection Model
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\ResourceModel\TaxTypes;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Digitalriver\DrPay\Model\TaxTypes;
use Digitalriver\DrPay\Model\ResourceModel\TaxTypes as TaxTypesResourceModel;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(TaxTypes::class, TaxTypesResourceModel::class);
    }
}
