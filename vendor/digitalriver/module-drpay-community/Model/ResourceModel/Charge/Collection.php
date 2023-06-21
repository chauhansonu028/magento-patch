<?php declare(strict_types=1);
/**
 * Catalog Sync Collection
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Model\ResourceModel\Charge;

use Digitalriver\DrPay\Model\Charge;
use Digitalriver\DrPay\Model\ResourceModel\Charge as ChargeResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Charge Collection
 */
class Collection extends AbstractCollection
{
    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(Charge::class, ChargeResourceModel::class);
    }
}
