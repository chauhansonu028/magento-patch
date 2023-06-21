/**
 *
 * DR Invoice Attribute mamnagement module
 *
 * @summary
 * Module which manages DR Invoice Attribute via backend
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

define([
    'uiComponent',
    'jquery',
    'mage/storage',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/action/recollect-shipping-rates',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/get-totals'
], function (
    Component,
    $,
    storage,
    errorProcessor,
    customer,
    customerData,
    urlBuilder,
    quote,
    totals,
    recollectShippingRates,
    getPaymentInformationAction,
    fullScreenLoader,
    getTotalsAction
) {
    'use strict';

    return {

        /**
         * Returns service Url
         *
         * @returns {string}
         */
        getClearInvoiceAttributeUrl: function () {
            if (!customer.isLoggedIn()) {
                return urlBuilder.createUrl('/dr/invoice-attribute/:cartId/clearInvoiceAttribute', {
                    cartId: quote.getQuoteId()
                });

            } else {
                return urlBuilder.createUrl('/dr/invoice-attribute/mine/clearInvoiceAttribute', {});
            }
        },


        /**
         * Returns assign DR Invoice Attribute service Url
         * @returns {string}
         */
        getAssignInvoiceAttributeUrl: function () {
            if (!customer.isLoggedIn()) {
              return urlBuilder.createUrl('/dr/invoice-attribute/:cartId/assignInvoiceAttribute', {
                  cartId: quote.getQuoteId(),
              });
            } else {
              return urlBuilder.createUrl('/dr/invoice-attribute/mine/assignInvoiceAttribute',{});
            }
        },

        /**
         * Removes DR Invoice Attributes from the quote in the backend
         */
        deleteInvoiceAttribute: function (deferred) {

            var self = this;
            var serviceUrl  = this.getClearInvoiceAttributeUrl();
            var promise = deferred || $.Deferred();

            storage.delete(
                serviceUrl,
                false
            ).done(function (response) {

                if (typeof window.checkoutConfig.payment.drpay_dropin.invoiceAttributeId != "undefined") {
                    delete window.checkoutConfig.payment.drpay_dropin.invoiceAttributeId;
                }

                promise.resolve();
            }).fail(function (response) {
                errorProcessor.process(response);
            });
        },

        /**
         * Assigns DR Invoice Attribute to the quote to the backend
         *
         * @param invoiceAttributeId
         * @param paymentMethod
         */
        assignInvoiceAttribute: function (invoiceAttributeId) {
            var self = this;

            fullScreenLoader.startLoader();

            storage.post(
                self.getAssignInvoiceAttributeUrl(),
                JSON.stringify({
                    invoiceAttributes: [{"invoiceAttributeId" : invoiceAttributeId}]
                })
            ).done(function (response) {
                window.checkoutConfig.payment.drpay_dropin.invoiceAttributeId = invoiceAttributeId;
                $('.dr-invoice-attribute-validation-message-success').show();
                $('.dr-invoice-attribute-validation-message-error').hide();
                if (jQuery('input[name="selected_card"]').length > 0) {
                    jQuery('.saved_cards_outer').show();
                }
                jQuery('#drop-in').show();
                jQuery(".dr-reminder-enter-invoice-tax-id").remove();

            }).fail(function (response) {
                $('.dr-invoice-attribute-validation-message-success').hide();
                $('.dr-invoice-attribute-validation-message-error').show();

                errorProcessor.process(response);
            }).always(
                function () {
                    fullScreenLoader.stopLoader();
                }
            );
        }
    };
});
