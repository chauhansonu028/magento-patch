<?php declare(strict_types=1);
/**
 * Refund Collection
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Model\ResourceModel\Refund;

use Digitalriver\DrPay\Model\Refund;
use Digitalriver\DrPay\Model\ResourceModel\Refund as RefundResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Refund Collection
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
        $this->_init(Refund::class, RefundResourceModel::class);
    }
}
