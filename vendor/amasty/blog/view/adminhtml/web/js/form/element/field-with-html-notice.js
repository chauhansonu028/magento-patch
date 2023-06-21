/**
 * Extend Abstract component
 */
define([
    'Magento_Ui/js/form/element/abstract',
    'mage/translate'
], function (Abstract, $t) {
    'use strict';

    return Abstract.extend({
        /**
         * @inheritDoc
         */
        initialize: function () {
            this._super();
            this.translateNotice();

            return this;
        },

        /**
         * @returns {void}
         */
        translateNotice: function () {
            this.set('notice', $t(this.notice()));
        }
    });
});
