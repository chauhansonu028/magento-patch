define([
    'Magento_Ui/js/dynamic-rows/record'
], function (Record) {
    'use strict';

    return Record.extend({
        initialize: function () {
            this._super();

            if (this.position === undefined) {
                var elementData = this.data(),
                    parentElement = this.parentComponent(),
                    identifierProperty = parentElement.identificationProperty;

                this.position = parentElement.pinnedElements[elementData[identifierProperty]]
                    || elementData['position']
                    || elementData[parentElement.positionProvider];
            }
        },

        initPosition: function (position) {
            var parentElement = this.parentComponent(),
                identifierProperty = parentElement.identificationProperty,
                recordData = this.data();

            parentElement.pinElement(+recordData[identifierProperty], +position);
            this._super(position);
        }
    });
});
