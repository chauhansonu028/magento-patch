define([
    'jquery',
    'uiComponent',
    'mage/url'
], function($, Component, urlBuilder) {
    'use strict';

    return Component.extend({
        defaults: {
            drPublicKey: '',
            locale: 'en-US',
            dropinConfiguration: {},
            dropinContainerId: 'drop-in'
        },

        loadDropinForm: function (billingAddress) {
            const self = this;
            if (billingAddress) {
                let DRObject = new DigitalRiver(self.drPublicKey, {"locale": self.locale});
                self.setDrConfiguration(DRObject, billingAddress);
            }
        },

        setDrConfiguration: function (DRObject, billingAddress) {
            const self = this;
            let drConfig = {
                options: {
                    flow: 'managePaymentMethods',
                    showSavePaymentAgreement: true
                },
                billingAddress: {
                    firstName: billingAddress.firstname,
                    lastName: billingAddress.lastname,
                    email: billingAddress.email,
                    phoneNumber: billingAddress.telephone,
                    address: {
                        line1: billingAddress.street[0],
                        line2: billingAddress.street[1],
                        city: billingAddress.city,
                        state: billingAddress.regionCode ?? billingAddress.region ?? '',
                        postalCode: billingAddress.postcode,
                        country: billingAddress.country_id
                    }
                },
                paymentMethodConfiguration: {
                    enabledPaymentMethods: ['creditCard'],
                    creditCard: {
                        style: {
                            base: {
                                color: "#333",
                                fontFamily: "Arial, Helvetica, sans-serif",
                                fontSize: "16px"
                            }
                        }
                    }
                },
                onSuccess: self.dropinSuccessCallback,
                onReady: self.dropinReadyCallback,
                onError: self.dropinErrorCallback,
                onCancel: self.dropinCancelCallback
            };

            $.extend(drConfig, self.dropinConfiguration);

            DRObject.createDropin(drConfig)
                    .mount(self.dropinContainerId);
        },

        dropinSuccessCallback: function(data) {
            if (data && data.readyForStorage) {
                $.ajax({
                    url: urlBuilder.build('/drpay/storedmethods/attach'),
                    type: 'POST',
                    showLoader: true,
                    data: {'source_id': data.source.id},
                    complete: function () {
                        location.reload();
                    },
                    error: function () {
                        console.log("api error");
                    }
                });
            }
        },
        dropinReadyCallback: function(data) {
            console.log(data);
        },

        dropinErrorCallback: function(data) {
            if(data.errors && data.errors.length > 0) {
                $('#dropin-message').html(data.errors[0].message);
            }
            console.log(data);
        },

        dropinCancelCallback: function(data) {
            console.log(data);
        }
    });
});
