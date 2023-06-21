/**
 * Resets DR Tax IDs
 *
 * @summary
 * Resets DR Tax IDs by removing them from the quote, before the shipping step address is saved
 *
 * @author   Vujadin Scepanovic <vujadin.scepanovic@rs.ey.com>
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
define([
    'mage/utils/wrapper'
], function (
    wrapper
) {
    'use strict';

    return function (shippingSaveProcessor) {

        if(!window.checkoutConfig.payment.drpay_dropin.is_active) {
            return shippingSaveProcessor;
        }
        
        shippingSaveProcessor.saveShippingInformation = wrapper.wrapSuper(
            shippingSaveProcessor.saveShippingInformation,
            function (type) {
                return this._super(type);
            }
        );

        return shippingSaveProcessor;
    };
});
