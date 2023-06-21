/**
 * Widget for live-search dropdown with suggestions
 */

define([
    'jquery',
    'underscore',
    'mage/template',
    'text!Amasty_Blog/template/blog-live-search.html'
], function ($, _, mageTemplate, suggestTemplate) {
    'use strict';

    $.widget('mage.amBlogSearch', {
        options: {
            liveSearchUrl: '',
            inputEventDelay: 300,
            minCharacterLength: 3,
            activeClass: '-live-active',
            processClass: '-live-process',
            selectors: {
                searchFormSelector: '[data-amblog-js="search"]',
                searchFormContainer: '[data-amblog-js="content"]',
                searchFormSuggestions: '[data-amblog-js="suggest-list"]'
            },
            keycodes: {
                ESCAPE: 27
            }
        },

        /**
         * @inheritDoc
         * @private
         * @return {void}
         */
        _create: function () {
            this._initVariables();
            this._initListeners();
        },

        /**
         * @private
         * @return {void}
         */
        _initVariables: function () {
            this.isListOpen = false;
            this.suggestions = null;
            this.hasOnOutsideClick = false;
            this.searchForm = this.element.closest(this.options.selectors.searchFormSelector);
            this.searchFormContainer = this.element.closest(this.options.selectors.searchFormContainer);
            this.searchSuggestionsList = this.searchFormContainer.find(this.options.selectors.searchFormSuggestions);
        },

        /**
         * @private
         * @return {void}
         */
        _initListeners: function () {
            var self = this;

            $(self.element).on('keyup', _.debounce(function () {
                // eslint-disable-next-line jquery-no-trim
                self._onPropertyChange($(this).val().trim());
            }, self.options.inputEventDelay));

            self.searchForm.on('keydown', function (event) {
                switch (event.keyCode) {
                    case self.options.keycodes.ESCAPE:
                        self.closeSuggestionsList();
                        // eslint-disable-next-line
                        $(this).parent().focus();

                        break;
                    default:
                        break;
                }
            });

            self.searchForm.on('submit', function () {
                self.element.val(self.element.val().trim());

                return true;
            });
        },

        /**
         * Check input value and call ajax if valid
         * @param {string} value
         * @private
         * @return {void}
         */
        _onPropertyChange: function (value) {
            var isLettersLengthPassed = value.toString().length >= this.options.minCharacterLength;

            if (_.isEmpty(value) || !isLettersLengthPassed) {
                this.closeSuggestionsList();
                this.toggleProcessState(false);

                return;
            }

            if (!_.isEmpty(value) && isLettersLengthPassed) {
                this._makeAjaxCall();
            }
        },

        /**
         * @private
         * @return {void}
         */
        _makeAjaxCall: function () {
            var self = this,
                data = self.searchForm.serializeArray();

            self.toggleProcessState(true);

            $.ajax({
                url: self.options.liveSearchUrl,
                data: data,
                type: 'post',
                dataType: 'json',
                success: function (response) {
                    if (response && !_.isEmpty(response)) {
                        self.showSuggestionsList(response);
                        self._setSuggestions(response);
                    }

                    self.toggleProcessState(false);
                }
            });
        },

        /**
         * @private
         * @param {object} suggestions
         * @return {mage.amBlogSearch}
         */
        _setSuggestions(suggestions) {
            this.suggestions = suggestions;

            return this;
        },

        /**
         * @private
         * @return {null|Object|*}
         */
        _getSuggestions() {
            return this.suggestions;
        },

        /**
         * @private
         * @param {object} suggestions
         * @return {Object|*|null|boolean}
         */
        _isEqualSuggestions(suggestions) {
            var self = this;

            return self._getSuggestions() && _.isEqual(suggestions, self._getSuggestions());
        },

        /**
         * build and append suggestions
         * @param {Object} suggestions
         * @return {void}
         */
        showSuggestionsList: function (suggestions) {
            if (!this._isEqualSuggestions(suggestions)) {
                this.searchSuggestionsList.html(this.getSuggestionsTemplate(suggestions));
            }

            this.searchSuggestionsList.show();

            if (!this.isListOpen) {
                this.setOpenState();
            }
        },

        /**
         * clear suggestions items
         * @return {void}
         */
        closeSuggestionsList: function () {
            this.searchSuggestionsList.hide();

            if (this.isListOpen) {
                this.setCloseState();
            }
        },

        /**
         * build the template with all suggestions
         * @param {object} suggestions
         * @return {*|jQuery|HTMLElement}
         */
        getSuggestionsTemplate: function (suggestions) {
            return $(mageTemplate(suggestTemplate, {
                data: suggestions
            }));
        },

        /**
         * @return {void}
         */
        setOpenState: function () {
            this.searchSuggestionsList.parent()
                .addClass(this.options.activeClass)
                .attr('aria-haspopup', true);

            this.searchSuggestionsList
                .addClass(this.options.activeClass)
                .attr('aria-hidden', false)
                .attr('aria-expanded', true)
                .attr('role', 'listbox')
                .attr('tabindex', 0);

            this.isListOpen = true;

            if (!this.hasOnOutsideClick) {
                this.onOutsideClick();
            }
        },

        /**
         * @return {void}
         */
        setCloseState: function () {
            this.searchSuggestionsList.parent()
                .removeClass(this.options.activeClass)
                .attr('aria-haspopup', false);

            this.searchSuggestionsList
                .removeClass(this.options.activeClass)
                .attr('aria-hidden', true)
                .attr('aria-expanded', false)
                .attr('role', '')
                .attr('tabindex', false);

            this.isListOpen = false;

            if (this.hasOnOutsideClick) {
                this.offOutsideClick();
            }
        },

        /**
         * @param {boolean} isInProcess
         * @return {mage.amBlogSearch}
         */
        toggleProcessState(isInProcess) {
            isInProcess
                ? this.searchFormContainer.addClass(this.options.processClass)
                : this.searchFormContainer.removeClass(this.options.processClass);

            return this;
        },

        /**
         * @return {void}
         */
        onOutsideClick: function () {
            var self = this;

            $(window).on('click.blogSearchSuggestionList', function (event) {
                if (self.isOutsideClick(event, $(self.searchForm).children())) {
                    self.closeSuggestionsList();
                }
            });

            this.hasOnOutsideClick = true;
        },

        /**
         * @return {void}
         */
        offOutsideClick: function () {
            $(window).off('click.blogSearchSuggestionList');

            this.hasOnOutsideClick = false;
        },

        /**
         * return true if click is happened outside of target elements
         * @param {Object} event - listener event
         * @param {Object} target - jQuery element
         * @returns {Boolean}
         */
        isOutsideClick: function (event, target)	{
            var clickedOut = true,
                i;

            for (i = 0; i < target.length; i++)  {
                if (event.target === target[i] || target[i].contains(event.target)) {
                    clickedOut = false;
                }
            }

            return clickedOut;
        }
    });

    return $.mage.amBlogSearch;
});
