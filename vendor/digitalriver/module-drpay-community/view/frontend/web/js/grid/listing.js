/**
 * Copyright (c) Gorilla, Inc. (http://www.gorillagroup.com)
 */

define([
    'Magento_Ui/js/grid/listing'
], function (gridListing) {
    'use strict';

    if(!window.DigitalRiver) {
        return gridListing;
    }

    return gridListing.extend({
        defaults: {
            template: 'Digitalriver_DrPay/grid/listing',
        }
    });
});
