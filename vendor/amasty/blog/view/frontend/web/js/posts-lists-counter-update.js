define([
    'jquery',
    'mage/url',
    'underscore',
    'mage/cookies',
    'pageCache',
    'mage/translate',
], function ($, urlBuilder, _) {
    'use strict';

    $.widget('amasty_blog.amBlogViewsList', {
        options: {
            baseUrl: window.BASE_URL,
            backendUrl: 'amblog/index/viewlist',
            selectors: {
                viewsCounterSelector: '[data-amblog-js="views-counter"]'
            }
        },

        _create: function () {
            var postsIds = this.getPostsIds();

            urlBuilder.setBaseUrl(this.options.baseUrl);
            $(this.options.selectors.viewsCounterSelector).formKey();

            if (postsIds.length > 0) {
                this.updateViewsCount(postsIds);
            }
        },

        /**
         *
         * @param {array} viewsCounts
         */
        updateViewsCounterValues: function (viewsCounts) {
            if (viewsCounts) {
                _.each(viewsCounts, function (count, postId) {
                    var counterText = $.mage.__('%1 view(s)').replace('%1', count);

                    $('[data-amblog-js="views-counter"][data-post-id="' + postId + '"]').html(counterText);
                });
            }
        },

        /**
         * @returns number[]
         */
        getPostsIds: function () {
            return $(this.options.selectors.viewsCounterSelector).toArray().map(function (element) {
               return Number($(element).attr('data-post-id'));
            });
        },

        /**
         *
         * @param {number[]} postsIds
         */
        updateViewsCount: function (postsIds) {
            $.ajax({
                method: 'POST',
                url: urlBuilder.build(this.options.backendUrl),
                data: {
                    form_key: $.mage.cookies.get('form_key'),
                    posts_ids: postsIds
                },
                success: function (result) {
                    this.updateViewsCounterValues(result['views_count_list']);
                }.bind(this)
            });
        }
    });

    return $.amasty_blog.amBlogViewsList;
});
