/**
 *  Amasty Blog Preview button Component
 */

define([
    'jquery',
    'uiRegistry',
    'mage/translate',
    'domReady!'
], function ($, registry, $t) {
    'use strict';

    $.widget('mage.amBlogPreviewButton', {
        options: {
            url: null,
            isBlogCacheEnabled: null,
            isNewPost: null,
            cacheStatusUrl: null
        },
        selectors: {
            actions: '.page-main-actions'
        },
        classes: {
            primary: 'action-primary',
            secondary: 'action-secondary'
        },
        nodes: {
            body: $('body'),
            modal: '<div class="ui-dialog-content ui-widget-content"></div>'
        },
        modalSettings: {
            newPostText: $t('Please make sure Amasty Blog cache is enabled. If it is disabled  the'
                + ' preview will not be available.'),
            existPostText: $t('Please note that when Amasty Blog cache is disabled the preview may not'
                + ' show all the latest changes. Enable the cache to view the current preview.')
        },

        /**
         * @inheritDoc
         */
        _create: function () {
            if (!this.options.isBlogCacheEnabled) {
                this._createModal();
            }

            this._handleClick();
        },

        /**
         * @private
         * @returns {void}
         */
        _handleClick: function () {
            var self = this;

            this.element.on('click', function (e) {
                e.preventDefault();

                if (self.options.isBlogCacheEnabled) {
                    self._invokePreview();
                } else {
                    self._checkCacheStatus();
                }

                return false;
            });
        },

        /**
         * @private
         * @returns {void}
         */
        _invokePreview: function () {
            if (this._validateForm()) {
                this._request();
            }
        },

        /**
         * @private
         * @returns {Boolean}
         */
        _validateForm: function () {
            this.form = registry.get('posts_form.posts_form');

            this.form.validate();

            return !this.form.additionalInvalid && !this.form.source.get('params.invalid');
        },

        /**
         * @private
         * @returns {Boolean}
         */
        _request: function () {
            $.ajax({
                url: this.options.url,
                data: this.form.source.get('data'),
                showLoader: true,
                success: this._successCallback
            });
        },

        /**
         * @private
         * @returns {Boolean}
         */
        _checkCacheStatus: function () {
            $.ajax({
                type: 'GET',
                url: this.options.cacheStatusUrl,
                showLoader: true,
                cache: false,
                data: {
                    'form_key': $.mage.cookies.get('form_key')
                },
                success: function (response) {
                    if (response.blogCacheStatus) {
                        this._invokePreview();
                    } else {
                        this._openModal();
                    }
                }.bind(this)
            });
        },

        /**
         * @private
         * @returns {Boolean | void}
         */
        // eslint-disable-next-line consistent-return
        _successCallback: function (response) {
            var self = this;

            if (response.ajaxExpired) {
                window.location.href = response.ajaxRedirect;
            }

            if (!response.error && response.url) {
                window.open(response.url, '_blank');

                return true;
            }

            self.nodes.body.notification('clear');

            $.each(response.messages, function (key, message) {
                self.nodes.body.notification('add', {
                    error: response.error,
                    message: message,
                    insertMethod: function (msg) {
                        $(self.selectors.actions).after(msg);
                    }
                });
            });
        },

        /**
         * @private
         * @returns {Array}
         */
        _getModalButtons: function () {
            var self = this,
                buttons = [ {
                    text: $t('Cancel'),
                    class: self.classes.secondary,
                    click: function () {
                        self._closeModal();
                    }
                } ];

            if (!self.options.isNewPost) {
                buttons.push({
                    text: $t('Preview'),
                    class: self.classes.primary,
                    click: function () {
                        self._closeModal();
                        self._invokePreview();
                    }
                });
            }

            return buttons;
        },

        /**
         * @private
         * @returns {void}
         */
        _openModal: function () {
            this.modalElement.modal('openModal');
        },

        /**
         * @private
         * @returns {void}
         */
        _closeModal: function () {
            this.modalElement.modal('closeModal');
        },

        /**
         * @private
         * @returns {void}
         */
        _createModal: function () {
            var self = this;

            this.modalElement = $(self.nodes.modal).modal({
                type: 'popup',
                subTitle: self.options.isNewPost ? self.modalSettings.newPostText : self.modalSettings.existPostText,
                buttons: self._getModalButtons()
            });
        }
    });

    return $.mage.amBlogPreviewButton;
});
