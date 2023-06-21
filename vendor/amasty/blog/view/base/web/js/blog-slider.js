/**
 *   Slick Slider functionality
 */

define([
    'jquery',
    'underscore',
    'amBlogSlick'
], function ($, _) {
    'use strict';

    $.widget('am.blogSlider', {
        options: {},
        classes: {
            loaded: '-am-loaded',
            slickInitialized: 'slick-initialized',
            sliderContainerClass: 'amblog-slider-container'
        },
        defaultSliderOptions: {
            slidesToShow: 3,
            responsive: [
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 2
                    }
                }
            ]
        },

        /**
         * @private
         * @returns {void}
         */
        _create: function () {
            this._initSlider();
            this._enableSlider();
        },

        /**
         * @public
         * @returns {void}
         */
        destroySlider: function () {
            if (this.element.hasClass(this.classes.slickInitialized)) {
                this.element.slick('unslick');
            }
        },

        /**
         * @private
         * @returns {void}
         */
        _initSlider: function () {
            var footerContainer = this.element.closest('.page-bottom');

            if (footerContainer.length) {
                footerContainer.addClass(this.classes.sliderContainerClass);
            }

            this.element.slick(_.extend(this.defaultSliderOptions, this.options));
        },

        /**
         * @private
         * @returns {void}
         */
        _enableSlider: function () {
            this.element.addClass(this.classes.loaded);

            if (this.element.hasClass(this.classes.slickInitialized)) {
                this.element.slick('setPosition');
            }
        }
    });

    return $.am.blogSlider;
});
