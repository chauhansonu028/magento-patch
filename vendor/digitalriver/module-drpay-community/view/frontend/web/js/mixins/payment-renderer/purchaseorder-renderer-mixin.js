define([
    'ko',
    'jquery',
], function (ko, $) {
    'use strict';

    return function (PurchaseOrderComponent) {

        if(!window.checkoutConfig.payment.drpay_dropin.is_active) {
            return PurchaseOrderComponent;
        }

        return PurchaseOrderComponent.extend({
            defaults: {
                template: 'Digitalriver_DrPay/payment/purchaseorder-form',
                imports: {
                    taxVatId: 'checkout.steps.billing-step.payment.payments-list.dr-tax-vat-id:taxVatId'
                },
            },

            /**
             * @return {Object}
             */
            getData: function () {
                var result = this._super();
                result.additional_data = $.extend(result.additional_data, {
                    tax_vat_id: this.taxVatId
                });

                return result;
            },
        });
    }
});
