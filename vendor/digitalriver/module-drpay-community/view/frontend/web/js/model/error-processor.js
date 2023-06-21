define([
    'jquery',
    'mage/url',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function ($, url, customerData, $t) {
    'use strict';

    return {
        /**
         * @param {Object} response
         * @param {Object} messageContainer
         */
        process: function (response) {
            var error;
            if (response.status == 401) { //eslint-disable-line eqeqeq
                window.location.replace(url.build('customer/account/login/'));
            } else {
                try {
                    error = JSON.parse(response.responseText);
                } catch (exception) {
                    error = {
                        message: $t('Something went wrong with your request. Please try again later.')
                    };
                }

                $.cookieStorage.set(
                    'mage-messages',
                    JSON.stringify([{
                        type: 'error',
                        text: error.message
                    }]));

                setTimeout(function () {
                    window.location.replace(url.build('checkout/cart'));
                }, 1);
            }
        }
    };
});
