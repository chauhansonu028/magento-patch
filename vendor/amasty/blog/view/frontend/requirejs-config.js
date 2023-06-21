var config = {
    map: {
        '*': {
            amBlogSlick: 'Amasty_Base/vendor/slick/slick.min',
            amasty_appendaround: 'Amasty_Blog/js/vendor/appendaround/appendaround',
            amBlogAccord : 'Amasty_Blog/js/amBlogAccord',
            amBlogViewStatistic: 'Amasty_Blog/js/amBlogViewStatistic',
            amBlogTabs: 'Amasty_Blog/js/tabs',
            amBlogViewsList: 'Amasty_Blog/js/posts-lists-counter-update',
            amBlogSearch: 'Amasty_Blog/js/blog-live-search',
            amBlogScrollTabs: 'Amasty_Blog/js/blog-scroll-tabs'
        }
    },
    paths: {
        catalogAddToCart: 'Magento_Catalog/js/catalog-add-to-cart'
    },
    shim: {
        amBlogSlick: {
            deps: [ 'jquery' ]
        }
    }
};
