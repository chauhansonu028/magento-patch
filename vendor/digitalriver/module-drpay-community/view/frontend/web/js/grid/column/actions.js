/**
 * Copyright (c) Gorilla, Inc. (http://www.gorillagroup.com)
 */

define([
    'Magento_Ui/js/grid/columns/actions',
    'mage/translate'
], function (gridActions, $t) {
    'use strict';

    if(!window.DigitalRiver) {
        return gridActions;
    }

    return gridActions.extend({
        defaults: {
            bodyTmpl: 'Digitalriver_DrPay/grid/cells/actions'
        }
    });
});
