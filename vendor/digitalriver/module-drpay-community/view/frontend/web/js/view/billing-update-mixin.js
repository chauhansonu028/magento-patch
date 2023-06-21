/**
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

define([
    'uiRegistry',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Digitalriver_DrPay/js/view/checkout/action/manage-tax-ids',
    'Digitalriver_DrPay/js/view/checkout/action/manage-invoice-attribute',
    'Magento_Checkout/js/action/set-billing-address',
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/action/get-payment-information'
], function (registry, $, quote, manageTaxIds, manageInvoiceAttribute, setBillingAddressAction, globalMessageList,getPaymentInformationAction) {
    'use strict';

    return function (Component) {

        if(!window.checkoutConfig.payment.drpay_dropin.is_active) {
            return Component;
        }

        return Component.extend({

            /**
             * @returns {Object}
             */
            initialize: function () {
                this._super();
            },

            updateAddresses: function () {

                // if address changes, clear existing tax ids & invoice attributes
                var manageTaxIdsDeferred = $.Deferred();
                manageTaxIds.deleteTaxIds(manageTaxIdsDeferred);
                var manageInvoiceAttributeDeferred = $.Deferred();
                manageInvoiceAttribute.deleteInvoiceAttribute(manageInvoiceAttributeDeferred);
                $.when(manageTaxIdsDeferred, manageInvoiceAttributeDeferred).done(function () {
                    var deferredBillingAddressActionDeferred = $.Deferred();
                    setBillingAddressAction(globalMessageList, deferredBillingAddressActionDeferred);
                });

                var isChecked = $('.payment-method._active .river-compliance-block-checkbox input').is(':checked'),
                    $button = $('.payment-method._active .actions-toolbar button.checkout');

                isChecked ? $button.removeClass('disabled') : $button.addClass('disabled')
            }
        });
    };
});
