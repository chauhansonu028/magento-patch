/**
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
/*browser:true*/

define([
        'ko',
        'jquery',
        'underscore',
        'mage/translate',
        'mage/url',
        'Digitalriver_DrPay/js/view/checkout/action/manage-tax-ids',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Digitalriver_DrPay/js/view/payment/action/place-order',
        'Digitalriver_DrPay/js/model/sca-challenge-handler',
        'Digitalriver_DrPay/js/model/error-processor',
    ],
    function (
        ko,
        $,
        _,
        t,
        mageUrl,
        manageTaxIds,
        fullScreenLoader,
        Component,
        quote,
        placeOrderAction,
        SCAChallengeHandler,
        errorProcessor
    ) {
        'use strict';

        var address = !quote.isVirtual()
            ? quote.shippingAddress
            : quote.billingAddress;

        return Component.extend({
            defaults: {
                template: 'Digitalriver_DrPay/payment/dropin',
                code: 'drpay_dropin',
                showManageTaxCertificatesLink: ko.observable(false),
            },
            redirectAfterPlaceOrder: false,

            initialize: function () {
                this._super();

                address.subscribe(
                    $.proxy(function (changes) {
                        if (!changes) {
                            return true;
                        }

                        this.showManageTaxCertificatesLink(changes.countryId === 'US');
                    }, this)
                );

                return this;
            },

            initObservable: function () {
                this._super()
                    .observe({
                        isBusinessEntity: false
                    });

                return this;
            },

            /** Redirect to custom controller for payment */
            afterPlaceOrder: function () {
                return false;
            },

            /**
             * Get payment name
             *
             * @returns {String}
             */
            getCode: function () {
                return this.code;
            },

            /**
             * Get payment title
             *
             * @returns {String}
             */
            getTitle: function () {
                return window.checkoutConfig.payment.drpay_dropin.title;
            },

            /**
             * Check if payment is active
             *
             * @returns {Boolean}
             */
            isActive: function () {
                var active = this.getCode() === this.isChecked();
                this.active(active);

                return active;
            },

            /**
             * @param isFirstTry
             * @returns {boolean}
             */
            placeOrder: function (isFirstTry) {
                var config = window.checkoutConfig.payment.drpay_dropin;

                var payload = {
                    paymentSessionId: config.payment_session_id,
                    checkoutId: config.checkoutId,
                    updateCheckout: false,
                };

                payload.savedSourceId = config.sourceId;
                if (isFirstTry) {
                    payload.primarySourceId = config.sourceId;
                    payload.updateCheckout = true;
                }

                placeOrderAction(JSON.stringify(payload))
                    .done($.proxy(this.placeOrderDone, this))
                    .fail($.proxy(this.placeOrderFail, this))
                    .always(function () {
                        $('body').trigger('processStop');
                    });

                return false;
            },

            /**
             * placeOrder response handler
             * @param response
             */
            placeOrderDone: function (response) {
                var additionalActionRequired = SCAChallengeHandler(
                    response,
                    $.proxy(function (data) {
                        if (data.status === 'complete') {
                            return this.placeOrder(false);
                        }
                        errorProcessor.process(response);
                    }, this)
                )

                if (!additionalActionRequired && response.status === 200) {
                    $.mage.redirect(response.redirect_url || mageUrl.build('checkout/onepage/success'));
                }
            },

            /**
             * This method can be used to handle error response
             * @param response
             */
            placeOrderFail: function (response) {
                errorProcessor.process(response);
            },

            radioInit: function () {
                $(".payment-methods input:radio:first").prop("checked", true).trigger("click");
            },

            setCustomerType: function () {
                if (jQuery('.opc-progress-bar .opc-progress-bar-item').length === 1) {
                    if (this.isBusinessEntity()) {
                        window.checkoutConfig.payment.drpay_dropin.taxIdType = {'type': 'business'};
                    } else {
                        window.checkoutConfig.payment.drpay_dropin.taxIdType = {'type': 'individual'};
                    }
                }
                return true;
            }
        }
    );
});
