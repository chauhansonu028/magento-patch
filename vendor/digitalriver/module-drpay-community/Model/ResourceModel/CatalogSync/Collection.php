<?php declare(strict_types=1);
/**
 * Catalog Sync Collection
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Model\ResourceModel\CatalogSync;

use Digitalriver\DrPay\Model\CatalogSync;
use Digitalriver\DrPay\Model\ResourceModel\CatalogSync as CatalogSyncResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * CatalogSync Collection
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
        $this->_init(CatalogSync::class, CatalogSyncResourceModel::class);
    }
}
