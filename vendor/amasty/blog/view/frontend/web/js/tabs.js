/**
 *  Amasty Blog Tabs Component
 */

define([
    'jquery',
], function ($) {
    'use strict';

    $.widget('mage.amBlogTabs', {
        options: {
            selectors: {
                tabLabel: '[data-amblog-js="tab-label"]',
                contentTabsBlock: '[data-amblog-js="content-tabs-block"]',
                contentTabs: '[data-amblog-js="content-tab"]',
                contentTabId: '[data-tab-id="%id"]',
                contentTabTitle: '[data-tab-title="%title"]'
            }
        },
        classes: {
            active: '-active',
        },
        nodes: {},

        /**
         * @returns {void}
         * @private
         */
        _create: function () {
            var self = this;

            self.nodes.contentTabsBlock = $(self.options.selectors.contentTabsBlock);
            self.nodes.contentTabs = self.nodes.contentTabsBlock.find(self.options.selectors.contentTabs);
            self.nodes.tabs = self.element.find(self.options.selectors.tabLabel);

            self.nodes.tabs.click(function () {
                self._toggleTab(this);
            });

            self._backToTabByHash();
        },

        /**
         * Toggle Tabs
         *
         * @params {object} item - clicked node element
         * @returns {void}
         * @private
         */
        _toggleTab: function (item) {
            var targetTabId = $(item).attr('data-tab-id'),
                targetTab = this.nodes.contentTabs.filter(this.options.selectors.contentTabId.replace('%id', targetTabId));

            this.nodes.tabs.removeClass(this.classes.active);
            $(item).addClass(this.classes.active);

            this.nodes.contentTabs.removeClass(this.classes.active);
            targetTab.addClass(this.classes.active);

            this._clearHash();
        },

        /**
         * Provide current active type id.
         *
         * @returns {number}
         * @public
         */
        getActiveTabId: function () {
            return +$(this.options.selectors.contentTabs + '.' + this.classes.active).attr('data-tab-id');
        },

        /**
         * Back to active tab after switch content page
         *
         * @returns {void}
         * @private
         */
        _backToTabByHash: function () {
            var hash = window.location.hash;

            if (hash) {
                this._toggleTab(this.nodes.tabs.filter(this.options.selectors.contentTabTitle.replace('%title', hash)));
            }
        },

        /**
         * Clear window hash
         *
         * @returns {void}
         * @private
         */
        _clearHash: function () {
            window.location.hash = '';
        },
    });

    return $.mage.amBlogTabs;
});
