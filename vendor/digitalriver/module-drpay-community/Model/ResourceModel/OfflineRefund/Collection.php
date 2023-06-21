<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\ResourceModel\OfflineRefund;

use Digitalriver\DrPay\Model\ResourceModel\OfflineRefund as OfflineRefundResourceModel;
use Digitalriver\DrPay\Model\OfflineRefund as OfflineRefundModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * OfflineRefund Collection
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
        $this->_init(OfflineRefundModel::class, OfflineRefundResourceModel::class);
    }
}
