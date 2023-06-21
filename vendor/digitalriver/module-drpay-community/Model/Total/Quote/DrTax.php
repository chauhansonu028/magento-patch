<?php
/**
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
namespace Digitalriver\DrPay\Model\Total\Quote;

use Digitalriver\DrPay\Helper\Config;
use Digitalriver\DrPay\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

class DrTax extends AbstractTotal
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        Session $checkoutSession,
        Data $helper,
        Config $config
    ) {
        $this->setCode('dr_tax');
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
        $this->config = $config;
    }

    /**
     * Collect totals process.
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        $address = $shippingAssignment->getShipping()->getAddress();
        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }

        if ($this->config->getIsEnabled() &&
            $address &&
            !$this->checkoutSession->getDrQuoteError() &&
            $this->checkoutSession->getDrCheckoutId()) {
            $drTax = $this->checkoutSession->getDrTax();
            $baseDrTax = $this->config->convertToBaseCurrency($drTax);
            $productTotal = $this->checkoutSession->getDrProductTotal();
            $productTotalExcl = $this->checkoutSession->getDrProductTotalExcl();
            $orderTotal = $this->checkoutSession->getDrOrderTotal();

            $shippingTotalInclTax = $this->checkoutSession->getDrShippingAndHandling();
            $shippingTax = $this->checkoutSession->getDrShippingTax();
            $total->setShippingInclTax($shippingTotalInclTax);
            $total->setShippingTaxAmount($shippingTax);

            $total->setBaseTaxAmount($baseDrTax);
            $total->setTaxAmount($drTax);
            $total->setSubtotalInclTax($productTotal);
            $total->setSubtotalExclTax($productTotalExcl);
            $total->setSubtotal($productTotalExcl);
            $total->setBaseSubtotal($this->config->convertToBaseCurrency($productTotalExcl));
            $total->setBaseGrandTotal($this->config->convertToBaseCurrency($orderTotal));
            $total->setGrandTotal($orderTotal);

            $address = $shippingAssignment->getShipping()->getAddress();
            $address->setBaseTaxAmount($baseDrTax);
            $address->setBaseSubtotalTotalInclTax($this->config->convertToBaseCurrency($productTotal));
            $address->setSubtotal($total->getSubtotal());
            $address->setBaseSubtotal($total->getBaseSubtotal());

            $total->setTotalAmount($this->getCode(), $drTax);
            $total->setBaseTotalAmount($this->getCode(), $baseDrTax);
        }

        return $this;
    }

    /**
     * Fetch (Retrieve data as array)
     *
     * @param Quote $quote
     * @param Total $total
     * @return array
     * @internal param \Magento\Quote\Model\Quote\Address $address
     */
    public function fetch(Quote $quote, Total $total)
    {
        $amount = $quote->getDrTax();
        if ($amount == 0) {
            $billingAddress = $quote->getBillingAddress();
            $amount = $billingAddress->getTaxAmount();
        }

        return [
            'code' => $this->getCode(),
            'title' => __('Tax'),
            'value' => $amount
        ];
    }
}
