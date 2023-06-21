/**
 *
 * DR Tax ID mamnagement module
 *
 * @summary
 * Module which manages DR Tax Ids via backend
 *
 * @author   Vujadin Scepanovic <vujadin.scepanovic@rs.ey.com>
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
        getClearTaxIdsUrl: function () {
            if (!customer.isLoggedIn()) {
                return urlBuilder.createUrl('/dr/tax-id/:cartId/clearTaxIds', {
                    cartId: quote.getQuoteId()
                });

            } else {
                return urlBuilder.createUrl('/dr/tax-id/mine/clearTaxIds', {});
            }
        },


        /**
         * Returns assign DR Tax ID service Url
         * @param customerType
         * @returns {string}
         */
        getAssignTaxIdsUrl: function (customerType) {
            if (!customer.isLoggedIn()) {
                return urlBuilder.createUrl('/dr/tax-id/:cartId/assignTaxId/:customerType', {
                    cartId: quote.getQuoteId(),
                    customerType: customerType
                });

            } else {
                return urlBuilder.createUrl('/dr/tax-id/mine/assignTaxId/:customerType', {
                    customerType: customerType
                });
            }
        },

        /**
         * Removes DR Tax Ids from the quote in the backend
         */
        deleteTaxIds: function (deferred) {
            var self = this;
            var  serviceUrl  = this.getClearTaxIdsUrl();
            var promise = deferred || $.Deferred();

            storage.delete(
                serviceUrl,
                false
            ).done(function (response) {

                if (typeof window.checkoutConfig.payment.drpay_dropin.taxid != "undefined") {
                    delete window.checkoutConfig.payment.drpay_dropin.taxid;
                    delete window.checkoutConfig.payment.drpay_dropin.taxidInputs;
                }
                // only reload totals if there was an id
                if(response != false) {
                    self.reloadTotals(promise);
                }

                promise.resolve();
            }).fail(function (response) {
                errorProcessor.process(response);
            });
        },

        reloadTotals: function (deferred) {
            var promise = deferred || $.Deferred();

            fullScreenLoader.startLoader();
            recollectShippingRates();
            getPaymentInformationAction(promise);
            $.when(promise).done(function () {
                fullScreenLoader.stopLoader();
                // The cart page totals summary block update
                getTotalsAction([], promise);
                if (jQuery('input[name="selected_card"]').length > 0) {
                    jQuery('.saved_cards_outer').show();
                }
                jQuery('#drop-in').show();
                jQuery(".dr-reminder-enter-invoice-tax-id").remove();
            });
            return promise;
        },

        /**
         * Assigns DR Tax Ids to the quote to the backend
         *
         * @param payload
         */
        assignTaxIds: function (payload) {
            var self = this;

            storage.post(
                self.getAssignTaxIdsUrl(payload.customerType),
                JSON.stringify({
                    taxIdentifiers: payload.taxIdentifiers
                })
            ).done(function (response) {
                $('.dr-tax-id-validation-message-success').show();
                $('.dr-tax-id-validation-message-error').hide();

                // show drop in if it's been hidden
                if(quote.paymentMethod().method === 'drpay_dropin')
                {
                    if (jQuery('input[name="selected_card"]').length > 0) {
                        jQuery('.saved_cards_outer').show();
                    }
                    jQuery('#drop-in').show();
                }

                self.reloadTotals();
            }).fail(function (response) {
                $('.dr-tax-id-validation-message-success').hide();
                $('.dr-tax-id-validation-message-error').show();

                errorProcessor.process(response);
            }).always(
                function () {
                    fullScreenLoader.stopLoader();
                }
            );
        }
    };
});
