define([
    'Magento_Ui/js/form/element/abstract',
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            imports: {
                priceType: "product_form.product_form_data_source:data.product.price_type"
            },
            listens: {
                priceType: 'priceTypeUpdateHandler'
            }
        },

        initialize: function () {
            return this._super();
        },

        priceTypeUpdateHandler: function (value) {
            var isDynamicPrice = parseInt(value) === 0;
            this.value(!isDynamicPrice ? this.value() : '');

            this.visible(!isDynamicPrice);
        }
    });
});
