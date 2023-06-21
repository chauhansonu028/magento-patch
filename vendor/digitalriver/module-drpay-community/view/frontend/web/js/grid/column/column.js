/**
 * Copyright (c) Gorilla, Inc. (http://www.gorillagroup.com)
 */

define([
    'Magento_Ui/js/grid/columns/column'
], function (gridColumn) {
    'use strict';

    if(!window.DigitalRiver) {
        return gridColumn;
    }

    return gridColumn.extend({
        defaults: {
            bodyTmpl: 'Digitalriver_DrPay/grid/cells/text'
        }
    });
});
