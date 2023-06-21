/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'mage/storage',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/get-payment-information'
], function ($, quote, storage, urlBuilder, customer, fullScreenLoader, getPaymentInformationAction) {
    'use strict';

    return function (deferred, email) {

        return storage.post(
            urlBuilder.createUrl('/customers/isEmailAvailable', {}),
            JSON.stringify({
                customerEmail: email
            }),
            false
        ).done(function (isEmailAvailable) {
            if (isEmailAvailable) {

                if(window.checkoutConfig.payment.drpay_dropin.is_active) {
                    // load drop in if billing address has been entered and is a virtual order
                    if(quote.isVirtual()) {
                        var billingAddress = quote.billingAddress();
                        if (billingAddress) {
                            var getPaymentInformationActionDeferred = $.Deferred();
                            getPaymentInformationAction(getPaymentInformationActionDeferred);
                            $.when(getPaymentInformationActionDeferred).done(function () {
                                var methodCode = quote.paymentMethod();
                                if (methodCode.method === 'drpay_dropin') {
                                    loadDropInFrame();
                                }
                            });
                        }
                    }
                }
                deferred.resolve();
            } else {
                deferred.reject();
            }
        }).fail(function () {
            deferred.reject();
        });
    };
});
