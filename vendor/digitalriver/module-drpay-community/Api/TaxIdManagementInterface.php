<?php
/**
 * TaxID management
 *
 * @summary
 * Represents a TaxID management interface for service layer
 * @author Vujadin Scepanovic <vujadin.scepanovic@rs.ey.com>
 *
 * @category Digitalriver
 * @package Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Api;

use Magento\Framework\Exception\LocalizedException;

interface TaxIdManagementInterface
{

    /**
     * Assigns Tax ID to the DR Checkout
     *
     * @param string $cartId
     * @param string $customerType
     * @param \Digitalriver\DrPay\Api\TaxIdInterface[] $taxIdentifiers
     * @return bool
     */
    public function assignTaxIds($cartId, $customerType, $taxIdentifiers);

    /**
     * Clears TaxIds from the quote
     *
     * @param string $cartId
     * @return bool
     * @throws LocalizedException
     */
    public function clearTaxIds($cartId);
}
