define([
    'jquery',
    'mage/mage'
], function ($) {
    'use strict';

    return function (config, element) {
        var dataForm = $(element).mage('fileElement', {});
        dataForm.mage('validation', config);
    };
});
