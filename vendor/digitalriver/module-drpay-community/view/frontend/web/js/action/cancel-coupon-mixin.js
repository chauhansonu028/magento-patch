/**
 * @summary
 * Reloads Drop In when coupon is removed
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


    return function (cancelCoupon) {

        if(!window.checkoutConfig.payment.drpay_dropin.is_active) {
            return cancelCoupon;
        }

        cancelCoupon.registerSuccessCallback(function() {
            loadDropIn();
        });
        return cancelCoupon;
    };
});
