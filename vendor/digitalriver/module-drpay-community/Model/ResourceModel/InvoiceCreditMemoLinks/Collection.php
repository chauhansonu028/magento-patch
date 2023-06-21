<?php
/**
 * DR File Links Collection Model
 *
 * @summary Collection class for DR invoice/cm file links
 * @author Vignesh Balasubramani <vignesh.balasubramani@gds.ey.com>
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\ResourceModel\InvoiceCreditMemoLinks;

use Digitalriver\DrPay\Model\InvoiceCreditMemoLinks;
use Digitalriver\DrPay\Model\ResourceModel\InvoiceCreditMemoLinks as InvoiceCreditMemoLinksResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(InvoiceCreditMemoLinks::class, InvoiceCreditMemoLinksResource::class);
    }
}
