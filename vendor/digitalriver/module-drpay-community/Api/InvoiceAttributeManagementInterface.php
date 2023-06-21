<?php
/**
 * InvoiceAttribute management
 *
 * @summary
 * Represents a InvoiceAttribute management interface for service layer
 * @author Vujadin Scepanovic <vujadin.scepanovic@rs.ey.com>
 *
 * @category Digitalriver
 * @package Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Api;

use Magento\Framework\Exception\LocalizedException;

interface InvoiceAttributeManagementInterface
{

    /**
     * Assigns InvoiceAttribute to the DR Checkout
     *
     * @param string $cartId
     * @param \Digitalriver\DrPay\Api\InvoiceAttributeInterface[] $invoiceAttributes
     * @return bool
     */
    public function assignInvoiceAttribute($cartId, $invoiceAttributes);

    /**
     * Clears InvoiceAttributes from the quote
     *
     * @param string $cartId
     * @return bool
     * @throws LocalizedException
     */
    public function clearInvoiceAttribute($cartId);
}
