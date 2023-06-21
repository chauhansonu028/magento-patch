define([
    'uiComponent',
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/additional-validators',
    'uiRegistry',
    'mage/validation'
], function (Component, ko, $, quote, additionalValidators, uiRegistry) {
    'use strict';

    const complianceValidationSelector = '.payment-method._active .checkout-digital-river-compliance-block input[type="checkbox"]';

    additionalValidators.registerValidator({
        validate: function (hideError) {
            var isValid = true;

            if (!$(complianceValidationSelector).length) {
                return true;
            }

            $(complianceValidationSelector)
                .each(function (index, element) {
                    if (!$.validator.validateSingleElement(element, {
                        errorElement: 'div',
                        hideError: hideError || false
                    })) {
                        isValid = false;
                    }
                });

            return isValid;
        }
    });

    return Component.extend({
        defaults: {
            buttonSelector: '.payment-method._active .actions-toolbar button.action.primary:not(#DR-loadSavedPayment)',
            template: "Digitalriver_DrPay/payment/compliance",
            complianceText: ko.observable(''),
            isComplianceChecked: ko.observable(false),
            showComplianceBox: ko.observable(false),
        },

        initialize: function () {
            this._super();

            this.isComplianceChecked.subscribe(
                $.proxy(function (newValue) {
                    var $button = $(this.buttonSelector);
                    !newValue
                        ? $button.addClass('disabled')
                        : $button.removeClass('disabled');
                }, this)
            );

            quote.paymentMethod.subscribe(
                $.proxy(function (data) {
                    if (!data) {
                        return;
                    }

                    this.buttonSelector = '.payment-method .payment-method-content.' + quote.paymentMethod().method + ' button.action.primary';

                    var shouldShowBox = data.method !== 'drpay_dropin';
                    if (shouldShowBox) {
                        this.disablePlaceOrderButton();
                    }
                    this.showComplianceBox(shouldShowBox);
                }, this)
            );

            quote.paymentMethod.valueHasMutated();

            return this;
        },

        afterRender: function () {
            var config = window.checkoutConfig.payment.drpay_dropin;
            var DR = new DigitalRiver(config.public_key, {
                "locale": config.mage_locale
            });

            var complianceDetails = DR.Compliance.getDetails(
                config.default_selling_entity,
                config.mage_locale
            );

            if(complianceDetails && complianceDetails.disclosure) {
                this.complianceText(complianceDetails.disclosure.confirmDisclosure.localizedText);
                this.disablePlaceOrderButton();
            }
        },
        disablePlaceOrderButton: function() {
            this.isComplianceChecked(false);
            this.isComplianceChecked.valueHasMutated();

            let $button = $(this.buttonSelector);
            $button.addClass('disabled');
        }
    });
});
