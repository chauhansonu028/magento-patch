<?php
/**
 * DR Invoice Attribute Model
 *
 * @summary Provides methods for managing DR Invoice Attributes.
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Api\InvoiceAttributeInterface;
use Magento\Framework\Exception\InvalidArgumentException;

class InvoiceAttribute implements InvoiceAttributeInterface
{
    const ERROR_CODE_INVOICE_ATTRIBUTE_ID_NOT_VALID = 1;

    /**
     * INVOICE_ATTRIBUTE id
     *
     * @var string
     */
    protected $invoiceAttributeId;

    /**
     * Sets INVOICE_ATTRIBUTE id
     *
     * @param string $invoiceAttributeId
     */
    public function setInvoiceAttributeId($invoiceAttributeId)
    {
        if (!$invoiceAttributeId) {
            throw new InvalidArgumentException(
                __('Invalid invoice attribute'),
                null,
                self::ERROR_CODE_INVOICE_ATTRIBUTE_ID_NOT_VALID
            );
        }

        $this->invoiceAttributeId = $invoiceAttributeId;
    }

    /**
     * Gets INVOICE_ATTRIBUTE id
     *
     * @return string
     */
    public function getInvoiceAttributeId()
    {
        return $this->invoiceAttributeId;
    }
}
