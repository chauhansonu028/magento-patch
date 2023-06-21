<?php
/**
 * CountryOfOrigin Collection
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\ResourceModel\CountryOfOrigin;

use Digitalriver\DrPay\Model\CountryOfOrigin;
use Digitalriver\DrPay\Model\ResourceModel\CountryOfOrigin as CountryOfOriginResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Country Collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'country_code';

    /**
     * @var string
     */
    protected $_eventPrefix = 'dr_country_of_origin';

    /**
     * @var string
     */
    protected $_eventObject = 'country_of_origin_collection';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(CountryOfOrigin::class, CountryOfOriginResource::class);
    }
}
