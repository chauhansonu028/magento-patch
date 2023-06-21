define(
    [
        'jquery'
    ]
    , function($){
        return function (config) {
            $(document).ready(function () {
                countryResponseData = config.countryData;
                if (countryResponseData) {
                    let countryField = document.getElementById('dr_country_of_origin');
                    let count;
                    for (count = 1; count < countryField.options.length; count++) {
                        let keyval = countryField.options[count].label;
                        countryField.options[count].label = countryResponseData[keyval];
                    }
                }
            });
        }
    });
