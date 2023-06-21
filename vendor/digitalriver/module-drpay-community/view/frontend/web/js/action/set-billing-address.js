/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Ui/js/model/messageList',
    ],
    function (
        $,
        quote,
        urlBuilder,
        storage,
        errorProcessor,
        customer,
        fullScreenLoader,
        getPaymentInformationAction,
        messageList
    ) {
        'use strict';

        return function (messageContainer, deferred) {
            var serviceUrl,
                payload;

            deferred = deferred || $.Deferred();

            /**
             * Checkout for guest and registered customer.
             */
            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/billing-address', {
                    cartId: quote.getQuoteId()
                });
                payload = {
                    cartId: quote.getQuoteId(),
                    address: quote.billingAddress()
                };
            } else {
                serviceUrl = urlBuilder.createUrl('/carts/mine/billing-address', {});
                payload = {
                    cartId: quote.getQuoteId(),
                    address: quote.billingAddress()
                };
            }

            fullScreenLoader.startLoader();
            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            ).done(
                function () {
                   var getPaymentInformationActionDeferred = $.Deferred();
                   getPaymentInformationAction(getPaymentInformationActionDeferred);
                   $.when(getPaymentInformationActionDeferred).done(function () {
                       var methodCode = quote.paymentMethod();
                       if (methodCode.method === 'drpay_dropin') {
                           // force drop in reload to get latest address
                           window.checkoutConfig.payment.drpay_dropin.payment_session_id = null;
                           loadDropInFrame();
                       } else if (typeof loadDrVatIdElement === "function") {
                           // load w/ all payment methods when the address changes
                           // only load if the function exists
                           loadDrVatIdElement();
                       }
                       fullScreenLoader.stopLoader();
                       deferred.resolve();
                   });
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response, messageContainer);
                    fullScreenLoader.stopLoader();
                    deferred.reject();
                }
            );
        };
    }
);
