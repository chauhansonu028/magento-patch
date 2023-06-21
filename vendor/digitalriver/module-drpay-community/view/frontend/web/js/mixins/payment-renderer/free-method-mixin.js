define([
    'ko',
    'jquery',
    'mage/storage',
    'mage/url',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'Digitalriver_DrPay/js/view/payment/action/place-order',
    'Digitalriver_DrPay/js/action/get-dr-checkout-data',
    'Digitalriver_DrPay/js/model/error-processor',
], function (
    ko,
    $,
    storage,
    mageUrl,
    customer,
    fullScreenLoader,
    urlBuilder,
    Component,
    quote,
    placeOrderAction,
    getDrCheckoutData,
    errorProcessor
) {
    'use strict';

    return function (FreeComponent) {

        if(!window.checkoutConfig.payment.drpay_dropin.is_active) {
            return FreeComponent;
        }

        return FreeComponent.extend({
            defaults: {
                template: 'Digitalriver_DrPay/payment/free-form',
            },

            /**
             * @returns {boolean}
             */
            placeOrder: function () {
                fullScreenLoader.startLoader();
                storage.post(this.getLockCheckoutIdUrl(), false)
                    .done($.proxy(function () {
                        getDrCheckoutData()
                            .done($.proxy(function (response) {
                                if (!response.success) {
                                    errorProcessor.process(response);
                                    return;
                                }

                                placeOrderAction(this.preparePayload(response.content))
                                    .done($.proxy(this.placeOrderDone, this))
                                    .fail($.proxy(this.placeOrderFail, this))
                                    .always(function () {
                                        $('body').trigger('processStop');
                                    });
                            }, this))
                    }, this))
                    .fail(function (response) {
                        fullScreenLoader.stopLoader();
                        errorProcessor.process(response);
                    });

                return false;
            },

            /**
             * placeOrder success response handler
             * @param response
             */
            placeOrderDone: function (response) {
                if (response.status === 200) {
                    $.mage.redirect(response.redirect_url || mageUrl.build('checkout/onepage/success'));
                }
            },

            /**
             * placeOrder failure response handler
             * @param xhr
             */
            placeOrderFail: function (xhr) {
                errorProcessor.process(xhr);
            },

            /**
             * Object- > string converter
             * @param data
             * @returns {string}
             */
            preparePayload: function (data) {
                return JSON.stringify({
                    paymentSessionId: data.paymentSessionId,
                    checkoutId: data.checkoutId,
                    primarySourceId: null,
                    updateCheckout: true,
                })
            },

            /**
             * @returns {string}
             */
            getLockCheckoutIdUrl: function () {
                if (!customer.isLoggedIn()) {
                    return urlBuilder.createUrl(
                        '/dr/sources/:cartId/locked-checkout-id',
                        { cartId: quote.getQuoteId() }
                    );
                }

                return urlBuilder.createUrl(
                    '/dr/sources/mine/locked-checkout-id',
                    {}
                );
            }
        });
    }
});
