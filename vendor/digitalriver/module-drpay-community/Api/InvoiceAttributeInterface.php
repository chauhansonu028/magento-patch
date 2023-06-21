<?php


namespace Digitalriver\DrPay\Api;

interface InvoiceAttributeInterface
{
    /**
     * Set InvoiceAttribute ID
     *
     * @param string $invoiceAttributeId
     * @return void
     */
    public function setInvoiceAttributeId($invoiceAttributeId);

    /**
     * Get InvoiceAttribute ID
     *
     * @return string
     */
    public function getInvoiceAttributeId();
}
