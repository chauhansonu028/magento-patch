define([
    'jquery',
], function ($) {
    return function (config) {
        $(document).ready(function () {

            $('body').on('change', '#export_filter_dr_tax_group', function () {
                $("#export_filter_dr_tax_type").prop('disabled', false);
                var dr_tax_group_selected_value = $(this).find(":selected").val();
                $('#export_filter_dr_tax_type' + " option:gt(0)").remove();

                let taxData = config.tax_values;

                if (dr_tax_group_selected_value !== '') {
                    $.each(taxData, function (index, value) {
                        if (value.dr_tax_group === dr_tax_group_selected_value) {
                            $('#export_filter_dr_tax_type').append($("<option></option>")
                                .attr("value", value.entity_id).text(value.dr_tax_type));
                        }
                    });
                    $('#export_filter_dr_tax_type' + " option:eq(0)").attr('selected', 'selected').change();
                }
            });

            $('body').on('focus', '#export_filter_dr_tax_type', function () {
                if ($("#export_filter_dr_tax_group").find(":selected").val() === '') {
                    alert("Tax Group should not be empty");
                    $("#export_filter_dr_tax_type").val('');
                    $("#export_filter_dr_tax_type").prop('disabled', true);
                }
            });
        });
    }
});
