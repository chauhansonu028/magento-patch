define([
    'uiComponent',
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Digitalriver_DrPay/js/view/checkout/action/manage-tax-ids',
    'Digitalriver_DrPay/js/view/checkout/action/manage-invoice-attribute',
    'Magento_Checkout/js/model/full-screen-loader',
], function (Component, ko, $, quote, manageTaxIds, manageInvoiceAttribute, fullScreenLoader) {
    'use strict';

    return Component.extend({
        defaults: {
            template: "Digitalriver_DrPay/payment/tax-vat-id",
            shouldShowTaxVatIdField: ko.observable(false),
            taxVatId: ko.observable(null),
            isVirtualQuote: ko.observable(quote.isVirtual()),
            isBusinessPurchase: ko.observable(false),
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

            manageTaxIds.deleteTaxIds();

            address.subscribe(
                $.proxy(function (changes) {

                    // if showing the place order screen, do not show the field again
                    if(viewPlaceOrder) {
                        return false;
                    }

                    this.shouldShowTaxVatIdField(true);

                    if (!changes) {
                        return true;
                    }

                    this.shouldShowTaxVatIdField(changes.countryId !== 'US');

                }, this)
            );

            return this;
        },

        setTaxVatIdPurchase: function () {
          let businessPurchase = this.isBusinessPurchase();
          let manageTaxIdsDeferred = $.Deferred();

          manageTaxIds.deleteTaxIds(manageTaxIdsDeferred)
            let manageInvoiceAttributeDeferred = $.Deferred();
            manageInvoiceAttribute.deleteInvoiceAttribute(manageInvoiceAttributeDeferred);

            $.when(manageTaxIdsDeferred, manageInvoiceAttributeDeferred).done(function () {
                if (businessPurchase) {
                    window.checkoutConfig.payment.drpay_dropin.taxIdType = {'type':'business'};
                } else {
                    window.checkoutConfig.payment.drpay_dropin.taxIdType = {'type':'individual'};
                };
                loadDropIn();
            });

          return true;
        },

        afterRender: function () {},

        validateTaxId: function () {
            $('.dr-tax-id-validation-message-success').hide();
            $('.dr-tax-id-validation-message-error').hide()

            fullScreenLoader.startLoader();
            var payload;

            if (window.checkoutConfig.payment.drpay_dropin.taxidInputs === 1) {
                var taxId = window.checkoutConfig.payment.drpay_dropin.taxid[0];
                payload = {
                    customerType: taxId.customerType,
                    taxIdentifiers: [{
                        type: taxId.type,
                        value: taxId.value
                    }]
                };

            } else if (window.checkoutConfig.payment.drpay_dropin.taxidInputs === 2) {

                var taxId = [];
                taxId = window.checkoutConfig.payment.drpay_dropin.taxid;

                if (taxId[1] !== undefined ) {
                    payload = {
                        customerType: taxId[0].customerType,
                        taxIdentifiers: [{
                            type: taxId[0].type,
                            value: taxId[0].value
                        },
                            {
                                type: taxId[1].type,
                                value: taxId[1].value
                            }]
                    };
                } else {
                    payload = {
                        customerType: taxId[0].customerType,
                        taxIdentifiers: [{
                            type: taxId[0].type,
                            value: taxId[0].value
                        }]
                    };
                }
            }
            manageTaxIds.assignTaxIds(payload);
        }
    });
});
