define(
    [
        'jquery',
        'Digitalriver_DrPay/js/utils/setoptions',
        'select2'
    ]
    , function($,setoptions){
        return function (config) {
            $(document).ready(function () {
                $("#dr_eccn_code").select2();

                $("#toggle_dr_eccn_code").next().after(`<div class="eccn-details-container">
    <div class="dr-classification-code-container">
        <div class="title-item">DR Classification Code</div>
        <div class="dr-classification-item">
            <p class="dr-classification-content"></p>
        </div>
    </div>
    <div class="dr-description-container">
        <div class="title-item">DR Description</div>
        <div class="dr-description-item">
            <p class="dr-description-content"></p>
        </div>
    </div>
    <div class="dr-notes-container">
        <div class="title-item">DR Notes</div class="title-item">
        <div class="dr-notes-item">
            <p class="dr-notes-content"></p>
        </div>
    </div>
</div>`);
                let eccnResponseData = config.eccnData;
                let eccnValue;
                $('#dr_eccn_code').on('change', function () {
                    eccnValue = this.value;
                    setoptions(eccnValue, eccnResponseData);
                });
            });
        }
    });
