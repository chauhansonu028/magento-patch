/**
 *   Scroll to tabs widget
 */

define([
    'jquery',
    'domReady!'
], function ($) {
    'use strict';

    $.widget('mage.amBlogScrollTabs', {
        options: {
            watchedElement: '',
            scrollToTabs: true,
            scrollToTabsDuration: 300,
            offsetTop: 30,
            selectors: {
                watchedElement: '[data-amblog-js="hash"]',
                pagerLink: '[data-amblog-js="pager-link"]'
            }
        },

        /**
         * Widget initialization
         * @private
         *
         * @returns {void}
         */
        _create: function () {
            this.pagerLinks = $(this.options.selectors.pagerLink);

            if (this.options.scrollToTabs) {
                this._scrollToTabs();
            }

            this._initListeners();
        },

        /**
         * @private
         * @returns {void}
         */
        _scrollToTabs: function () {
            var $element;

            if (!window.location.hash) {
                return;
            }

            $element = $('[href="' + location.hash + '"]');

            if ($element.length) {
                $('html, body').stop().animate({
                    scrollTop: $element.offset().top - this.options.offsetTop
                }, this.options.scrollToTabsDuration, function () {
                    $element.trigger('click');
                });
            }
        },

        /**
         * @private
         * @return {void}
         */
        _initListeners: function () {
            var self = this,
                currentTabHref;

            $(this.options.selectors.watchedElement).on('click', function () {
                currentTabHref = $(this).attr('href');

                $.each(self.pagerLinks, function (i, el) {
                    var newHref = $(el).attr('href').split('#')[0] + currentTabHref;

                    $(el).attr('href', newHref);
                });

                window.location.hash = currentTabHref;
            });
        }
    });

    return $.mage.amBlogScrollTabs;
});
