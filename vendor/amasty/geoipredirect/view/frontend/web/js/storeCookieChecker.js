require([
    "jquery",
    'mage/cookies'
], function ($, modal) {
    'use strict';

    function checkStoreCookie()
    {
        if ($.mage.cookies.get('store')) {
            return;
        }
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('___store')) {
            var options = {'lifetime': (86400 * 30), 'samesite': 'Lax'},
                store = urlParams.get('___store');
            $.mage.cookies.set('store', store, options);
        }
    }

    return checkStoreCookie();
});
