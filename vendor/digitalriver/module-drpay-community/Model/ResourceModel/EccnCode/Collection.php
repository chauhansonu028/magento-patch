<?php
/**
 * EccnCode Collection
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\ResourceModel\EccnCode;

use Digitalriver\DrPay\Model\EccnCode;
use Digitalriver\DrPay\Model\ResourceModel\EccnCode as EccnCodeResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Eccn Collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'classification_code';

    /**
     * @var string
     */
    protected $_eventPrefix = 'dr_eccn_code';

    /**
     * @var string
     */
    protected $_eventObject = 'eccn_code_collection';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(EccnCode::class, EccnCodeResource::class);
    }
}
