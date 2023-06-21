/*global define*/
define([
    'ko',
    'jquery',
    'underscore',
    'mage/translate',
    'mage/storage',
    'Magento_Checkout/js/model/error-processor',
    'uiComponent',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/quote'
], function (
    ko,
    $,
    _,
    t,
    storage,
    errorProcessor,
    Component,
    fullScreenLoader,
    quote
) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Digitalriver_DrPay/checkout/summary/dr-tax-id-checkbox'
        },
        initObservable: function () {
            this._super()
                .observe({
                    isBusinessEntityShipping: false,
                });

            return this;
        },
        setCustomerTypeShipping: function () {
            if (this.isBusinessEntityShipping()) {
                window.checkoutConfig.payment.drpay_dropin.taxIdType = {'type':'business'};
            } else {
                window.checkoutConfig.payment.drpay_dropin.taxIdType = {'type':'individual'};
            }
            return true;
        },
        setDefaultCustomerTypeShipping: function () {
            window.checkoutConfig.payment.drpay_dropin.taxIdType = {'type':'individual'};
        }
    });
});
