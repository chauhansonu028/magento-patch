let setEccnCode;

define([
    'jquery'
], function ($) {
    setEccnCode = function (value, eccnResponseData) {

        if(value === '-- Select One --'){
            $('.dr-classification-content').text('');
            $('.dr-description-content').text('');
            $('.dr-notes-content').text('');
        }else {
            if(eccnResponseData){
                for(let i = 0; i < eccnResponseData.length; i++) {
                    let eccnAttributesData = eccnResponseData[i];
                    if(eccnAttributesData.classification_code === value){
                        $('.dr-classification-content').text(eccnAttributesData.classification_code);
                        $('.dr-description-content').text(eccnAttributesData.description);
                        $('.dr-notes-content').text(eccnAttributesData.notes);
                    }
                }
            }
        }
    }
    return setEccnCode;
});
