var config = {
    shim: {
        recommendationsSDK: {
            exports: "recsSDK",
        },
    },
    paths: {
        recommendationsSDK: "https://magento-recs-sdk.adobe.net/v2/index",
        recommendationsEvents: ['https://commerce.adobedtm.com/recommendations/events/v1/recommendationsEvents.min', 'Magento_ProductRecommendationsLayout/js/noopRecommendationsEvents'],
    },
}
