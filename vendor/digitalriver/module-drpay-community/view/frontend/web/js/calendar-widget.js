define([
    'jquery',
    'mage/translate',
    'mage/calendar'
], function ($, $t) {
    'use strict';

    return function (config, element) {
        $(element).calendar({
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            currentText: $t('Go Today'),
            closeText: $t('Close'),
            showWeek: true
        });
    };
});
