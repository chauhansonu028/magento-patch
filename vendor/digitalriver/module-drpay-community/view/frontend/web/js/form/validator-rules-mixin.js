define(["jquery",
    'Magento_Checkout/js/model/quote'
], function ($, quote, ) {
    "use strict";

    return function (validator) {

        if(!window.checkoutConfig.payment.drpay_dropin.is_active) {
            return validator;
        }

        validator.addRule("validate-deps-business-purchase",
            function (value) {
                var isTW = jQuery("div[name='shippingAddress.country_id'] select[name='country_id'] option:selected").val() === 'TW';
                var isbusinessPurchase;
                if (!quote.isVirtual()) {
                    if (typeof jQuery("div[name='billingAddressdrpay_dropin.country_id'] select[name='country_id'] option:selected").val() !== 'undefined') {
                        isTW = jQuery("div[name='billingAddressdrpay_dropin.country_id'] select[name='country_id'] option:selected").val() === 'TW';
                    }
                    isbusinessPurchase = !!document.querySelector("#is-customer-business-shipping")?.checked;
                } else {
                    isTW = jQuery("div[name='billingAddressdrpay_dropin.country_id'] select[name='country_id'] option:selected").val() === 'TW';
                    isbusinessPurchase = !!document.querySelector("#dr-vat-id-input-purchase-containerdrpay_dropin")?.checked;
                }
                if (isbusinessPurchase && isTW) {
                    return value.length >= 2;
                }

                return true;
            },
            $.mage.__("This is a required field.")
        );
        return validator;
    };
});