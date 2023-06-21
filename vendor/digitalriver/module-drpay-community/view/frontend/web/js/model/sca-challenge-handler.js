define([], function () {
    'use strict';

    return function (response, callback) {
        var responseStatus = [response.status, response.code].join('_');

        var actionIsRequired = "409_additional_payment_action_required" === responseStatus;
        if (actionIsRequired) {
            var config = window.checkoutConfig.payment.drpay_dropin;

            let DRPayment = new DigitalRiver(config.public_key, { "locale": config.mage_locale });
            DRPayment.handleNextAction({
                "action": "sca_required",
                "data": {
                    "sessionId": config.payment_session_id
                }
            }).then(callback);
        }

        return actionIsRequired;
    }
})
