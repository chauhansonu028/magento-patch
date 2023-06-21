/**
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/billing-address': {
                'Digitalriver_DrPay/js/view/billing-update-mixin': true
            },
            'Magento_Checkout/js/model/shipping-save-processor': {
                'Digitalriver_DrPay/js/view/shipping-update-mixin': true
            },
            'Magento_OfflinePayments/js/view/payment/method-renderer/purchaseorder-method': {
                'Digitalriver_DrPay/js/mixins/payment-renderer/purchaseorder-renderer-mixin': true
            },
            'Magento_Payment/js/view/payment/method-renderer/free-method': {
                'Digitalriver_DrPay/js/mixins/payment-renderer/free-method-mixin': true
            },
            'Magento_SalesRule/js/action/set-coupon-code': {
                'Digitalriver_DrPay/js/action/set-coupon-code-mixin': true
            },
            'Magento_SalesRule/js/action/cancel-coupon': {
                'Digitalriver_DrPay/js/action/cancel-coupon-mixin': true
            },
            "Magento_Ui/js/lib/validation/validator": {
                "Digitalriver_DrPay/js/form/validator-rules-mixin": true,
            },
        }
    },
    map: {
        '*': {
            'DigitalRiverCalendarWidget': 'Digitalriver_DrPay/js/calendar-widget',
            'DigitalRiverComplianceWidget': 'Digitalriver_DrPay/js/compliance-text-widget',
            'DigitalRiverComplianceSubmitButtonState': 'Digitalriver_DrPay/js/compliance-submit-button-state',
            'DigitalRiverNewCertificateValidationWidget': 'Digitalriver_DrPay/js/new-certificate-validation-widget',
            'Magento_Checkout/js/model/step-navigator': 'Digitalriver_DrPay/js/model/step-navigator',
            'Magento_Checkout/js/action/set-billing-address': 'Digitalriver_DrPay/js/action/set-billing-address',
            'Magento_Customer/js/action/check-email-availability': 'Digitalriver_DrPay/js/action/check-email-availability',
            'uiDigitalriverStoredMethodsListing': 'Digitalriver_DrPay/js/grid/listing',
            'uiDigitalriverStoredMethodsColumn': 'Digitalriver_DrPay/js/grid/column/column',
            'uiDigitalriverStoredMethodsActions': 'Digitalriver_DrPay/js/grid/column/actions'
        }
    }
};
