define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    './refund-offline/styles-config',
    'mage/storage',
    'Magento_Customer/js/customer-data',
    'https://js.digitalriverws.com/v1/DigitalRiver.js',
], function ($, modal, drStylesConfig, storage, customerData) {
    return function (config, element) {
        var $element = $(element);
        var DR = new DigitalRiver(config.drPublicKey, {"locale": config.drLocale});

        var $modalElement = $element.find('.dr-refund-modal');
        var elementModal = modal({
            buttons: [],
            opened: function () {
                var $this = $(this);
                if ($this.data('dr-offline-ready')) {
                    return;
                }

                // to avoid duplication of initialization
                $this
                    .append($('<div id="dr-refund-element-content" style="margin-bottom: 30px;"></div>'))
                    .data('dr-offline-ready', true);

                var offlineRefund = DR.createElement('offlinerefund', $.extend(config.drOptions, drStylesConfig));
                offlineRefund.mount('dr-refund-element-content');
                offlineRefund.on('change', function(data) {
                    if (data.complete) {
                        storage
                            .put(config.completeTokenEndpoint.trim())
                            .done(function () {
                                setTimeout(function() {
                                    elementModal.closeModal();
                                    var message = $.mage.__('Your information has been successfully submitted.' +
                                        'Please allow 2 to 4 weeks for your refund to appear in your account');

                                    customerData.set('messages', {
                                        messages: [{
                                            type: 'success',
                                            text: message
                                        }]
                                    });
                                }, 500);
                            });
                    }
                });
            }
        }, $modalElement);

        $element.on('click', '.btn', function () {
            elementModal.openModal();
        })
    };
});
