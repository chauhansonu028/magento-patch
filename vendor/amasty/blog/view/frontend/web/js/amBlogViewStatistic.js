define([
    'jquery',
    'mage/url',
    'mage/cookies',
    'pageCache',
    'mage/translate',
    'Magento_Customer/js/customer-data'
], function ($, urlBuilder) {
    'use strict';

    $.widget('amasty_blog.amBlogViewStatistic', {
        options: {
            postId: null,
            baseUrl: window.BASE_URL,
            backendUrl: 'amblog/index/view'
        },

        _create: function () {
            urlBuilder.setBaseUrl(this.options.baseUrl);
            this.element.formKey();
            this.updateViewsCount()
        },

        /**
         *
         * @param {number} viewsCount
         */
        updateViewsCounterValue: function (viewsCount) {
            if (!isNaN(viewsCount)) {
                this.element.html($.mage.__('%1 view(s)').replace('%1', viewsCount));
            }
        },

        updateViewsCount: function () {
            $.ajax({
                method: 'GET',
                url: urlBuilder.build(this.options.backendUrl),
                cache: false,
                data: {
                    form_key: $.mage.cookies.get('form_key'),
                    post_id: this.options.postId
                },
                success: function (result) {
                    this.updateViewsCounterValue(Number(result['views_count']));
                }.bind(this)
            });
        }
    });

    return $.amasty_blog.amBlogViewStatistic;
});
