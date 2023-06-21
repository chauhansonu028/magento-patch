/**
 * @summary
 * Reloads Drop In when coupon is added
 *
 * @author   Matt Schroeder <mschroeder@digitalriver.com>
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
define([
    'mage/utils/wrapper',
    'mage/translate'
], function (
    wrapper,
    $t
) {
    'use strict';


    return function (setCouponCode) {

        if(!window.checkoutConfig.payment.drpay_dropin.is_active) {
            return setCouponCode;
        }

        setCouponCode.registerSuccessCallback(function() {
            loadDropIn();
        });
        return setCouponCode;
    };
});
