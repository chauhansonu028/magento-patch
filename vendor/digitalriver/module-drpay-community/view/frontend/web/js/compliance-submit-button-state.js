define([
    'jquery',
], function ($, $t) {
    'use strict';

    return function (config, element) {
        if (!window.DigitalRiver) {
            return false;
        }

        var $complianceCheckbox = $(config.checkboxSelector);
        if (!$complianceCheckbox.length) {
            return;
        }

        $(document).on('change', config.checkboxSelector, function () {
            var $this = $(this);
            var $button = $(config.submitButtonSelector);

            if ($this.is(':checked')) {
                $button.removeClass('disabled');
                return;
            }

            $button.addClass('disabled');
        });
    };
});
