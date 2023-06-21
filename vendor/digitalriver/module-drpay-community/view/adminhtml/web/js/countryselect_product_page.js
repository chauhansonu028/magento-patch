define(
    [
        'jquery',
        'uiRegistry',
    ]
    , function($, uiRegistry){
        return function (config) {
            let set = false;
            $(document).ajaxComplete(function () {
                if(!set){
                    set = true;
                    let countryResponseData = config.countryData;
                    let countryField = uiRegistry.get('index = dr_country_of_origin');
                    if(countryResponseData && countryField){
                        let count;
                        for(count = 1; count < countryField.initialOptions.length; count++ ){
                            let countryCode = countryField.initialOptions[count].label;
                            countryField.initialOptions[count].label = countryResponseData[countryCode];
                        }
                    }
                }
            });
        }
    });
