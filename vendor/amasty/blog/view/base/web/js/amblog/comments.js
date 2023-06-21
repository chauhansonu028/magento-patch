/**
 * Blog comments logic
 */

define([
    'jquery',
    'mage/translate',
    'mage/cookies',
    'mage/validation',
    'pageCache'
], function ($, $t) {
    'use strict';

    $.widget('mage.amblogComments', {
        options: {
            form_selector: null,
            form_url: null,
            update_url: null,
            post_url: null,
            post_id: null,
            session_id: null,
            maxDeep: 2
        },
        selectors: {
            form: '[data-amblog-js="form"]',
            reply: '[data-amblog-js="reply-to"]',
            commentsWrapper: '[data-amblog-js="comments_wrapper"]',
            topCommentsWrapper: '[data-amblog-js="top-comments-wrapper"]',
            shortComments: '[data-amblog-js="short_comments"]',
            gdpr: '[data-amblog-js="gdpr-agree"]',
            formHead: '[data-amblog-js="form-head"]',
            toggleRepliesButton: '[data-amblog-js="toggle-replies"]',
            commentsForm: '[data-amblog-js="comments-form"]',
            commentsCount: '[data-amblog-js="comments-count"]',
            firstLevelReplies: '[data-amblog-js="replies-block"] > [data-amblog-js="replies"]',
            replies: '[data-amblog-js="replies"]',
            comment: '[data-amblog-js="comment-item"]',
            cancel: '[data-amblog-js="close-form"]'
        },
        classes: {
            active: '-active',
            empty: '-empty',
            loaded: '-loaded',
            highlighted: '-highlighted',
            comment: 'am-blog-comment',
            deepReply: 'deep-reply'
        },
        texts: {
            hideReplies: $t('Hide replies')
        },
        nodes: {
            formHeadBlock: $('<div>', {
                class: 'amblog-form-head'
            })
        },

        _create: function () {
            var self = this;

            $(document).on('submit', self.selectors.form, function (e) {
                if ($(this).validation() && $(this).validation('isValid')) {
                    self.submitForm($(this).serializeArray(), $(this));
                }

                e.preventDefault();
                e.stopPropagation();

                return false;
            });

            $(document).on('click', self.selectors.reply, function () {
                self.showForm('am-comment-form-' + $(this).attr('data-id'), $(this).attr('data-id'));
            });

            $(document).on('click', self.selectors.cancel, function () {
                $(this).parents(self.selectors.form).parent().hide();
            });

            this.loadCommentsByAjax();
            this.initTogglingCommentsListener();
            this.setReplyCommentsCount();
        },

        /**
         * Toggle reply comments block
         *
         * @public
         * @returns {void}
         */
        initTogglingCommentsListener: function () {
            var self = this,
                counter,
                buttonTitle;

            $(document).on('click', self.selectors.toggleRepliesButton, function () {
                counter = $(this).find(self.selectors.commentsCount);

                if (!$(this).hasClass(self.classes.active)) {
                    buttonTitle = counter.html();
                    $(this).addClass(self.classes.active);
                    counter.html(self.texts.hideReplies);
                } else {
                    $(this).removeClass(self.classes.active);
                    counter.html(buttonTitle);
                }

                $('[data-reply-id="' + $(this).attr('data-id') + '"]').toggleClass(self.classes.active);
            });
        },

        /**
         * Set reply comments count to the toggle button and the form header
         *
         * @public
         * @returns {void}
         */
        setReplyCommentsCount: function () {
            var self = this,
                repliesCount,
                button,
                replyText;

            $(self.selectors.firstLevelReplies).each(function (index, element) {
                repliesCount = $(element).find(self.selectors.comment).length;
                button = $(self.selectors.toggleRepliesButton + '[data-id="' + element.dataset.replyId + '"]');
                replyText = repliesCount > 1 ? $t('replies') : $t('reply');

                if (!repliesCount) {
                    button.addClass(self.classes.empty);
                }

                button.find(self.selectors.commentsCount).text($t('%1')
                    .replace('%1', repliesCount + ' ' + replyText));
            });
        },

        initialize: function (params) {
            this.form = false;

            // Redirect to form after login
            if (window.location.hash) {
                if (window.location.hash === '#add-comment') {
                    this.showForm('amblog-comment-form', 0);
                } else if (window.location.hash.indexOf('#reply-to-') !== -1) {
                    var replyTo = window.location.hash.replace('#reply-to-', '');

                    this.showForm('am-comment-form-' + replyTo, replyTo);
                }
            }
        },

        loadCommentsByAjax: function () {
            var self = this;

            $.mage.formKey();

            $.ajax({
                type: 'GET',
                cache: false,
                url: this.options.update_url,
                data: {
                    'post_id': this.options.post_id,
                    'form_key': $.mage.cookies.get('form_key')
                },
                success: function (data) {
                    try {
                        if (data.content) {
                            var tmpElement = $('<div>').html(data.content).find(self.selectors.commentsWrapper),
                                element = $(self.selectors.commentsWrapper);

                            if (element && tmpElement && tmpElement.children().length) {
                                element.html(tmpElement.html());
                            }

                            if (data.short_content) {
                                var tmpElement = $('<div>').html(data.short_content).find(self.selectors.shortComments),
                                    element = $(self.selectors.shortComments);

                                if (element && tmpElement && tmpElement.children().length) {
                                    element.html(tmpElement.html());
                                }
                            }

                            if (data.comments_form) {
                                var commentsFormTmpElement = $('<div>').html(data.comments_form),
                                    commentsForm = $(self.selectors.commentsForm);

                                if (commentsForm && commentsFormTmpElement.length) {
                                    commentsForm.html(commentsFormTmpElement.children());
                                }
                            }

                            self.setReplyCommentsCount();
                            self.commentsLoadedCallback();
                            self.setDeepReplyClass();
                            self.scrollToComment();
                            element.trigger('contentUpdated');
                        } else if (data.error) {
                            console.warn(data.error);
                        }
                    } catch (e) {
                        var response = {};
                    }
                }
            });
        },

        hideForm: function (formId, callback) {
            var form = typeof formId === 'string' ? $(formId) : formId;

            form.innerHTML = '';
            // eslint-disable-next-line no-new
            new Effect.Fade(form, {
                afterFinish: typeof callback != 'undefined' ? callback() : function () {
                },
                duration: 1.0
            });
        },

        showForm: function (container, id) {
            var formContainer = $('#' + container);

            if (formContainer && formContainer.css('display') === 'none') {
                $(this.form_selector).each(function (element) {
                    if (element.id !== container) {
                        element.innerHTML = '';
                        $(element).hide();
                    }
                });

                this.showLoader(formContainer);

                $(formContainer).show();

                this.loadFormToContainer(container, id);
            }

            return false;
        },

        showLoader: function (who) {
            $(who).append('<div class="amblog-loader"></div>');
        },

        /**
         * Adding class when comments is loaded
         *
         * @public
         * @returns {void}
         */
        commentsLoadedCallback: function () {
            $(this.selectors.commentsWrapper).addClass(this.classes.loaded);
        },

        /**
         * Set class for styling especially deep replies
         * @return {void}
         */
        setDeepReplyClass: function () {
            var self = this,
                replies = this.element.find(this.selectors.replies);

            $.each(replies, function () {
                if ($(this).parents(self.selectors.replies).length >= self.options.maxDeep) {
                    $(this).addClass(self.classes.deepReply);
                }
            });
        },

        /**
         * Scrolling to comment by url hash after page is loaded
         *
         * @public
         * @returns {void}
         */
        scrollToComment: function () {
            var self = this,
                element = $(window.location.hash),
                elementReplies = element.closest(this.selectors.replies);

            if (window.location.hash.indexOf(this.classes.comment) === -1) {
                return;
            }

            if (element.is(':hidden')) {
                elementReplies.addClass(self.classes.active);
                $(self.selectors.toggleRepliesButton + '[data-id="' + elementReplies.attr('data-reply-id') + '"]')
                    .addClass(self.classes.active);
            }

            self.scrollToElement(element[0]);
            self.highlightComment(element, true);
            self.unHighlightListener(element);
        },

        /**
         * Adding class when comments is loaded
         *
         * @param {Object} element - Jquery element
         * @param {Boolean} flag - true to highlight, false to remove highlight
         * @public
         * @returns {void}
         */
        highlightComment: function (element, flag) {
            element.toggleClass(this.classes.highlighted, flag);
        },

        /**
         * Listener to remove highlight from an element by clicking or hovering
         *
         * @param {Object} element - Jquery element
         * @public
         * @returns {void}
         */
        unHighlightListener: function (element) {
            element.on('hover click', function () {
                this.highlightComment(element, false);
                element.off('hover click');
            }.bind(this));
        },

        /**
         * Scrolling to an element
         *
         * @param {HTMLElement} element - target
         * @public
         * @returns {void}
         */
        scrollToElement: function (element) {
            var offset = 60,
                elementPosition = element.offsetTop,
                offsetPosition = elementPosition - offset;

            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        },

        loadFormToContainer: function (container, id) {
            var url = decodeURI(this.options.form_url.replace(/^http[s]{0,1}/, window.location.href.replace(/:[^:].*$/i, ''))),
                self = this;

            $.ajax({
                type: 'GET',
                url: url.replace('{{post_id}}', this.options.post_id).replace('{{session_id}}', this.options.session_id).replace('{{reply_to}}', id).replace(/^http[s]{0,1}/, window.location.href.replace(/:[^:].*$/i, '')),
                success: function (data) {
                    try {
                        if (data.form) {
                            $('#' + container).html(data.form);
                        }
                    } catch (e) {
                        var response = {};
                    }
                }
            });
        },

        submitForm: function (formValues, form) {
            var values = {},
                url = decodeURI(this.options.post_url),
                self = this;

            $.each(formValues, function (i, field) {
                values[field.name] = field.value;
            });

            if (form.find(self.selectors.gdpr).length
                && !form.find(self.selectors.gdpr + ':checked').length
            ) {
                values.email = '';
                values.name = '';
                values['customer_id'] = 0;
            }

            if (!('name' in values)) {
                values.name = '';
            }

            url = url.replace('{{post_id}}', this.options.post_id)
                .replace('{{session_id}}', this.options.session_id)
                .replace('{{reply_to}}', values.reply_to)
                .replace(/^http[s]{0,1}/, window.location.href.replace(/:[^:].*$/i, ''));
            $.ajax({
                type: 'POST',
                data: values,
                url: url,
                beforeSend: function () {
                    $('body').loader('show');
                },

                success: function (response) {
                    $('body').loader('hide');

                    if (response.error == 1) {
                        window.scrollTo(0, 0);

                        return false;
                    }

                    form.find('textarea').val('');

                    if (!form.parents().eq(1).is(self.selectors.topCommentsWrapper)) {
                        form.parent().hide();
                        $(response.message).insertBefore(form.parent()).show();
                    } else {
                        $(response.message).insertAfter(form.parent()).show();
                    }

                    $(self.selectors.commentsWrapper).trigger('contentUpdated');

                    self.setReplyCommentsCount();
                },

                error: function () {
                    $('body').loader('hide');
                    self._scrollToTop();
                }
            });
        },

        _scrollToTop: function () {
            $('html,body').animate({
                scrollTop: 0
            }, 'slow');
        }
    });

    return $.mage.amblogComments;
});
