<?php
/**
 * DR Invoice Credit memo links Resource Model
 *
 * @summary Provides hyper links for invoice and credit memo.
 * @author Vignesh Balasubramani <vignesh.balasubramani@gds.ey.com>
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * DR Invoice Credit Memo File Links Resource Model
 * Class InvoiceCreditMemoLinks
 */
class InvoiceCreditMemoLinks extends AbstractDb
{

    const DR_INVOICE_CM_LINKS = 'dr_invoice_cm_links';

    const DR_INVOICE_CM_LINKS_ID_FIELD_NAME = 'entity_id';

    /**
     * Initialize connection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(self::DR_INVOICE_CM_LINKS, self::DR_INVOICE_CM_LINKS_ID_FIELD_NAME);
    }
}
