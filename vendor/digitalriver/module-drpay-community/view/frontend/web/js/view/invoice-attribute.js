define([
    'uiComponent',
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Digitalriver_DrPay/js/view/checkout/action/manage-invoice-attribute',
    'Magento_Checkout/js/model/full-screen-loader',
], function (Component, ko, $, quote, manageInvoiceAttribute, fullScreenLoader) {
    'use strict';

    return Component.extend({
        defaults: { //dr-invoice-button-container
            template: "Digitalriver_DrPay/payment/invoice-attribute",
            shouldShowInvoiceAttributeField: ko.observable(true),
            invoiceAttribute: ko.observable(null),
            isVirtualQuote: ko.observable(quote.isVirtual()),
            businessEntity: false,
            imports: {
                businessEntity: 'checkout.steps.shipping-step.shippingAddress.before-form.dr-tax-id-checkbox-container:isBusinessEntityShipping',
            }
        },

        initObservable: function () {
            return this._super().observe({
                businessEntity: true,
            })
        },

        initialize: function () {
            this._super();

            var address = !quote.isVirtual()
                ? quote.shippingAddress
                : quote.billingAddress;

            manageInvoiceAttribute.deleteInvoiceAttribute();

            address.subscribe(
                $.proxy(function (changes) {
                    // if showing the place order screen, do not show the field again
                    if(viewPlaceOrder) {
                        return false;
                    }

                    if(!changes) {
                        return true;
                    }
                    this.shouldShowInvoiceAttributeField(true);
                    window.checkoutConfig.payment.drpay_dropin.shouldShowInvoiceAttributeField = this.shouldShowInvoiceAttributeField();
                }, this)
            );

            return this;
        },
        afterRender: function () {}
    });
});
