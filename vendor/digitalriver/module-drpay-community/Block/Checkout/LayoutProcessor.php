<?php

namespace Digitalriver\DrPay\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var \Digitalriver\DrPay\Helper\Config
     */
    protected $config;

    public function __construct(
        \Digitalriver\DrPay\Helper\Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        $isEnabled = $this->config->getIsEnabled();

        // unset components within checkout_index_index if DrPay is not enabled
        if (!$isEnabled) {
            unset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children']['dr-tax-vat-id']);
            unset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children']['dr-invoice-attribute']);
            unset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children']['dr-set-payment-information-after']);
            unset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children']['dr-compliance']);

            unset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['renders']['children']['drpay_dropin-payment']);

            unset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['before-form']['children']['dr-tax-id-checkbox-container']);
            unset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['before-form']['children']['drfooter']);

            unset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']
                ['totals']['children']['duty_and_tariffs']);
            unset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']
                ['totals']['children']['dr_fees']);

            //unset validation
            unset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children']['drpay_dropin-form']);
            unset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['shipping-address-fieldset']['children']['company']
                ['validation']['validate-deps-business-purchase']);

            // unset dr fees
            unset($jsLayout['components']['block-totals']['children']['duty_and_tariffs']);
            unset($jsLayout['components']['block-totals']['children']['dr_fees']);

        }

        return $jsLayout;
    }
}
