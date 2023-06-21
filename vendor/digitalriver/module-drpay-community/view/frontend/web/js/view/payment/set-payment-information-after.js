define([
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/payment/place-order-hooks',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Checkout/js/model/quote',
    'Digitalriver_DrPay/js/view/checkout/action/manage-tax-ids',
    'Digitalriver_DrPay/js/view/checkout/action/manage-invoice-attribute',
    'Magento_Checkout/js/model/full-screen-loader'
], function ($, Component, hooks, getTotals, quote, manageTaxIds, manageInvoiceAttribute, fullScreenLoader) {
    'use strict';

    return Component.extend({

        initialize: function () {
            this._super();
            var latestPaymentMethod = quote.paymentMethod();
            var paymentInformationSetAfter = function () {
                var paymentMethod = quote.paymentMethod();
                // when the payment method changes and there's an entered billing address
                if (paymentMethod && latestPaymentMethod !== paymentMethod.method && quote.billingAddress()) {
                    // delete tax ids & invoice attributes when payment methods change
                    let manageTaxIdsDeferred = $.Deferred();
                    manageTaxIds.deleteTaxIds(manageTaxIdsDeferred);
                    let manageInvoiceAttributeDeferred = $.Deferred();
                    manageInvoiceAttribute.deleteInvoiceAttribute(manageInvoiceAttributeDeferred);

                    $.when(manageTaxIdsDeferred,manageInvoiceAttributeDeferred).done(function () {
                        getTotals([loadDropInFrame.bind(this)]);
                    });
                    latestPaymentMethod = paymentMethod.method;
                }
            };
            hooks.afterRequestListeners.push(paymentInformationSetAfter);
        }
    })
})
