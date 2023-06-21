/**
 *
 * Control to render value in Cart/Checkout for Duty Fee item
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
define(
    [
        'jquery',
        'underscore',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Magento_Catalog/js/price-utils',
        'Magento_Customer/js/customer-data',
        'Magento_Ui/js/view/messages',
        'mage/translate'
    ],
    function ($, _, Component, quote, totals, priceUtils, customerData) {
        "use strict";

        return Component.extend({
            defaults: {
                template: 'Digitalriver_DrPay/checkout/summary/duty_fee_and_tariffs'
            },

            totals: quote.getTotals(),

            isDisplayedTotal: function () {
                var drIorValue = parseFloat(totals.getSegment('dr_ior').value);
                // val should be 0 or 1 based on boolean session value
                return drIorValue == 1 ? true : false;
            },

            getTotalValue: function () {
                var dutyFeeSegmentValue = this.getSegmentValue('dr_duty_fee')
                var tariffsValue = this.getSegmentValue('dr_ior_tax');

                return this.getFormattedPrice(dutyFeeSegmentValue + tariffsValue);
            },

            getSegmentValue: function (code) {
                var value = totals.getSegment(code).value;
                return parseFloat((value !== '' && value > 0) ? value : 0.00);
            },

            getTitle: function () {
                return this.title;
            }
        });
    }
);
