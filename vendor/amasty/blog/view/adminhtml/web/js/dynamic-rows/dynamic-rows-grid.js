define([
    'Magento_Ui/js/dynamic-rows/dynamic-rows-grid',
    'underscore',
    'prototype'
], function (dynamicRowsGrid, _) {
    'use strict';

    return dynamicRowsGrid.extend({
        pinnedElements: {},

        processingInsertData: function () {
            this._super();
            this._sort();
        },

        _sort: function () {
            var positionData = _.indexBy(this.recalculatePositionData(), this.identificationProperty);

            this.elems(this.elems().sort(function (propOne, propTwo) {
                var firstRowData = propOne.data(),
                    firstId = firstRowData[this.identificationProperty],
                    secondRowData = propTwo.data(),
                    secondId = secondRowData[this.identificationProperty],
                    firstPosition,
                    secondPosition;

                firstPosition = _.has(this.pinnedElements, firstId)
                    ? this.pinnedElements[firstId]
                    : ~~propOne.position;
                secondPosition = _.has(this.pinnedElements, secondId)
                    ? this.pinnedElements[secondId]
                    : ~~propTwo.position;

                return firstPosition - secondPosition;
            }.bind(this)));
        },

        setToInsertData: function () {
            var dnd = this.dnd(),
                dndEnabled = dnd && dnd.enabled;

            if (dndEnabled && !this.update) {
                this.source.set(this.dataProvider, this.recalculatePositionData());
            } else if (!dndEnabled) {
                this._super();
            }
        },

        /**
         * @return {array}
         */
        recalculatePositionData: function () {
            var allPositions = _.map(this.relatedData, function (element) {
                    return +element[this.positionProvider];
                }.bind(this)),
                pinnedPositions = _.values(this.pinnedElements),
                freePositions = _.difference(allPositions, pinnedPositions).reverse(),
                positionData;

            positionData = this.relatedData.map(function (element) {
                var resultObject = {},
                    identifier = element[this.identificationProperty];

                resultObject[this.positionProvider] = _.has(this.pinnedElements, identifier)
                    ? this.pinnedElements[identifier]
                    : freePositions.pop();
                resultObject[this.identificationProperty] = identifier;

                return resultObject;
            }.bind(this));

            return _.sortBy(positionData, this.positionProvider);
        },

        /**
         *
         * @param {number} elementIdentifier
         * @param {number} position
         */
        pinElement: function (elementIdentifier, position) {
            this.pinnedElements[elementIdentifier] = position;
        },

        /**
         *
         * @param {number} elementIdentifier
         */
        unpinElement: function (elementIdentifier) {
            delete this.pinnedElements[elementIdentifier];
        },

        /**
         *
         * @param {number} index
         * @param {number} recordId
         */
        deleteRecord: function (index, recordId) {
            this._super(index, recordId);
        },

        /**
         * Update data for send after sort
         *
         * @param {number|string}position
         * @param {object} elem
         * @return {*}
         */
        sort: function (position, elem) {
            var result = this._super(position, elem);

            this.setToInsertData();

            return result;
        },

        reload: function () {
            this._super();
            this.pages(Math.ceil(this.relatedData.length / this.pageSize));
        }
    });
});
