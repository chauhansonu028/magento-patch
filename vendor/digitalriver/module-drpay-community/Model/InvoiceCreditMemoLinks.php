<?php
/**
 * DR Invoice Credit memo file links Model
 *
 * @summary Provides Invoice and Credit Memo file link information.
 * @author Vignesh Balasubramani <vignesh.balasubramani@gds.ey.com>
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model;

use Magento\Framework\Model\AbstractModel;
use Digitalriver\DrPay\Model\ResourceModel\InvoiceCreditMemoLinks as InvoiceCreditMemoLinksResource;

/**
 * DR Invoice and Credit Memo File Links class
 * Class InvoiceCreditMemoLinks
 */
class InvoiceCreditMemoLinks extends AbstractModel
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(InvoiceCreditMemoLinksResource::class);
    }
}
