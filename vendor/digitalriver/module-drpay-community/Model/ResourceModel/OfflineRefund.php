<?php

declare(strict_types=1);

namespace Digitalriver\DrPay\Model\ResourceModel;

use Digitalriver\DrPay\Api\Data\OfflineRefundInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * OfflineRefund sync Resource model.
 */
class OfflineRefund extends AbstractDb
{
    /**
     * Define Table name for resource entity
     */
    public const DIGITAL_RIVER_OFFLINE_REFUND = 'dr_offline_refund';
    /**
     * ID is received from Digital River
     *
     * @var bool
     */
    protected $_isPkAutoIncrement = false;
    /**
     * Model should consider this flag, as id is set manually during API request
     *
     * @var bool
     */
    protected $_useIsObjectNew = true;

    /**
     * Initialize strategic resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(self::DIGITAL_RIVER_OFFLINE_REFUND, OfflineRefundInterface::FIELD_DR_REFUND_ID);
    }
}
