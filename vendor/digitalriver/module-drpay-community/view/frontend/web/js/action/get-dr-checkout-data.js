define([
    'jquery',
    'mage/url'
], function ($, urlBuilder) {
    'use strict';

    return function () {
        return $.ajax({
            type: 'GET',
            showLoader: true,
            contentType: 'application/json',
            url: urlBuilder.build('drpay/payment/savedrquote'),
        });
    }
})
