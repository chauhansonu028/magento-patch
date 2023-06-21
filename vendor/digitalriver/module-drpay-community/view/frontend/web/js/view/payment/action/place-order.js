define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/url-builder',
    'mage/url',
], function ($, quote, customer, urlBuilder, mageUrl) {
    'use strict';

    if(!window.checkoutConfig.payment.drpay_dropin.is_active) {
        return;
    }

    return function (data, actions) {
        var placeOrderUrl = customer.isLoggedIn()
            ? '/dr/quotes/mine/placeOrder'
            : '/dr/guest-quotes/:cartId/placeOrder';

        var url = urlBuilder.createUrl(placeOrderUrl, {cartId: quote.getQuoteId()})

        return $.ajax({
            url: mageUrl.build(url),
            method: "PUT",
            statusCode: actions,
            showLoader: true,
            contentType: 'application/json',
            data: data
        });
    }
});
