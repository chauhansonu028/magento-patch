define([
    'jquery',
    'Digitalriver_DrPay/js/model/storedmethods/dropin',
    'jquery-ui-modules/widget'
], function($, Dropin) {
    'use strict';

    $.widget('dr.addStoredPaymentMethod', {
        options: {
            drPublicKey: '',
            locale: '',
            defaultSellingEntity: '',
            regionJson: {},
            customerEmail: '',
            billingAddressContainer: '',
            billingAddressForm: '',
            addMethodBtn: '',
            saveAddressBtn: '',
        },
        _create: function(){
            const _self = this;

            this.$addMethodBtn = $(this.options.addMethodBtn);
            this.$addressForm = $(this.options.billingAddressForm);
            this.$saveAddressbtn = $(this.options.saveAddressBtn);
            this.$storeMethodsWrapper = $('.stored-methods__wrapper');
            this.$billingAddressContainer = $(this.options.billingAddressContainer);
            this.$addCardheading = $('.stored-methods__heading--hidden');

            this.$addMethodBtn.on('click', function(){
                _self.$addMethodBtn.css('display', 'none');
                _self.$storeMethodsWrapper.find('.table-wrapper').css('display', 'none');
                _self.addMethod();
            });

            this.$saveAddressbtn.on('click', function(e){
                e.preventDefault();

                // proceed to credit card form if address form is valid
                if (_self.$addressForm.valid()) {
                    _self.saveAddress();
                }
            });
        },

        addMethod: function(){

            // Initialize DR compliance (footer)
            const _self = this;

            let digitalriverpayments = new DigitalRiver(_self.options.drPublicKey, {
                locale: _self.options.locale
            });

            let complianceOptions = {
                classes: {
                    base: 'DRElement'
                },
                compliance: {
                    locale:  _self.options.locale,
                    entity:  _self.options.defaultSellingEntity
                }
            };

            let compliance = digitalriverpayments.createElement('compliance', complianceOptions);

            compliance.mount('compliance');

            // Show billing address form
            this.$billingAddressContainer.css('display', 'block');
        },

        saveAddress: function () {
            const _self = this;
            this.$billingAddressContainer.css('display', 'none');
            this.$addMethodBtn.css('display', 'none');
            this.$addCardheading.css('display', 'block');

            let billingAddress = {
                email: _self.options.customerEmail,
                street: []
            };

            /**
             * Mod Start
             * Get country id from billing address form instead of billingAddress object,
             * as this is declared above with only email value
             * Get regions list for address country before loop
             */
            let $billingAddressForm = $(_self.options.billingAddressForm);
            let countryId = $billingAddressForm.find('#country').val();
            let regions = _self.options.regionJson[countryId];

            $.each($billingAddressForm.serializeArray(), function (index, value) {
                if (value.name === 'region_id' && typeof regions !== 'undefined') {
                    billingAddress.regionCode = regions[value.value] ? regions[value.value].code : '';
                }
                if (value.name.indexOf('street') !== -1) {
                    billingAddress['street'].push(value.value);
                } else {
                    billingAddress[value.name] = value.value;
                }
            });
            /* Mod End */

            Dropin({
                drPublicKey: _self.options.drPublicKey,
                locale: _self.options.locale
            }).loadDropinForm(billingAddress);

            $('.stored-methods__drop-in').addClass('loaded');
        }
    });
    return $.dr.addStoredPaymentMethod;
});
