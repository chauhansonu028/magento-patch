define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    'use strict';

    return function (config, element) {
        if (!window.DigitalRiver) {
            return false;
        }

        var DR = new DigitalRiver(config.public_key, {
            "locale": config.mage_locale
        });

        var complianceDetails = DR.Compliance.getDetails(
            config.default_selling_entity,
            config.mage_locale
        );

        if (complianceDetails && complianceDetails.disclosure) {
            var privacyPolicyData = complianceDetails.disclosure.privacyPolicy;
            var businessEntityName = complianceDetails.disclosure.businessEntity.name;
            var agreeText = $t('I agree with the $1 of $2');

            var privacyPolicyLink = '<a href="$1" target="_blank">$2</a>'
                .replace('$1', privacyPolicyData.url)
                .replace('$2', privacyPolicyData.localizedText);

            $(element).html(agreeText
                .replace('$1', privacyPolicyLink)
                .replace('$2', businessEntityName));
        }
    };
});
