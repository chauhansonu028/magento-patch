define([
    'Magento_Ui/js/form/element/multiselect'
], function (Multiselect) {
    'use strict';

    return Multiselect.extend({
        normalizeData: function (value) {
            var values =  this._super(),
                options = this.options();

            if (values instanceof Array && options instanceof Array) {
                values = values.map(function (value) {
                    var option = options.find(function (currentOption) {
                        return +currentOption.value === +value;
                    });

                    return option ? option.label : value;
                });
            }

            return values;
        }
    });
});
