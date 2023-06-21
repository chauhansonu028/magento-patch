define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/ui-select',
    'Magento_Ui/js/modal/modal',
    'Digitalriver_DrPay/js/utils/setoptions',
], function (_, uiRegistry, select, modal,setoptions) {
    'use strict';
    let eccnResponseData = false;
    return select.extend({

        initialize: function (){
            /*Gets ECCN details in Product Edit page*/
            let urlEccn = window.drPayEccnUrl;

            jQuery.ajax({
                url: urlEccn,
                type: 'GET',
                complete: function(response) {
                    eccnResponseData = response.responseJSON;
                },
                error: function (xhr, status, errorThrown) {
                    console.log('Error: Unable to get attribute details');
                }
            });
            return this._super();
        },

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            setoptions(value, eccnResponseData);
            return this._super();
        }
    });
});
