var config = {
    map: {
        '*': {
            amBlogSlick: 'Amasty_Base/vendor/slick/slick.min',
            amBlogSlider: 'Amasty_Blog/js/blog-slider',
            amBlogDates: 'Amasty_Blog/js/components/amblog-humanize-dates'
        }
    },
    shim: {
        amBlogSlider: {
            deps: [ 'Amasty_Base/vendor/slick/slick.min' ]
        },
        amBlogSlick: {
            deps: [ 'jquery' ]
        }
    }
};
