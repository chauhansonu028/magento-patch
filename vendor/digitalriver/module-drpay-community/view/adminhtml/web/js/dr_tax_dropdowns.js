/**
 *
 * Populate dr tax type select options which are dependent on selected dr tax group select option.
 * Handle various edge cases on product add/edit and attribute bulk update page.
 * Prevent inconsistent options' states before save.
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
define([
    'jquery'
], function ($) {
    'use strict';

    return function (config) {
        var click_handler_added = 0;
        var change_handler_added = 0;

        $(document).ajaxComplete(function () {

            var product_bulk_attribute_update_page = jQuery("#dr_tax_group").length !== 0 && jQuery("#dr_tax_type").length !== 0;

            if (product_bulk_attribute_update_page) {

                addSelectsEventHandlers('#dr_tax_group', '#dr_tax_type');

                change_handler_added = 1;

                jQuery('#toggle_dr_tax_group').change(function() {
                    if(this.checked) {
                        jQuery("#toggle_dr_tax_type").prop("checked", true).change();
                        jQuery("#dr_tax_type").prop('disabled', false);
                    } else {
                        jQuery("#toggle_dr_tax_type").prop("checked", false).change();
                        jQuery("#dr_tax_type").prop('disabled', true);
                        jQuery("#dr_tax_type option:gt(0)").remove();
                        jQuery("#dr_tax_group option:eq(0)").attr('selected','selected').change();
                    }
                });

                jQuery('#toggle_dr_tax_type').change(function(e) {
                    e.preventDefault();
                    if (jQuery("#toggle_dr_tax_type").prop('checked') !== true) {
                        if (jQuery("#dr_tax_type").find(":selected").val() === '') {
                            if (jQuery("#dr_tax_group").find(":selected").val() === '') {
                                jQuery("#toggle_dr_tax_type").prop('checked', false);
                                jQuery("#toggle_dr_tax_group").prop('checked', false);
                                jQuery("#dr_tax_group").prop('disabled', true);
                                jQuery("#dr_tax_type option:gt(0)").remove();
                                jQuery("#dr_tax_group option:eq(0)").attr('selected','selected').change();

                            } else {
                                alert('Digital River Tax Type is a required attribute');
                                jQuery("#toggle_dr_tax_type").prop('checked', true);
                                jQuery("#dr_tax_type").prop('disabled', false);
                            }

                        } else {
                            jQuery("#toggle_dr_tax_type").prop('checked', false);
                            jQuery("#toggle_dr_tax_group").prop('checked', false);
                            jQuery("#dr_tax_group").prop('disabled', true);
                            jQuery("#dr_tax_type option:gt(0)").remove();
                            jQuery("#dr_tax_group option:eq(0)").attr('selected','selected').change();
                        }
                    } else {
                        jQuery("#toggle_dr_tax_type").prop('checked', true);
                    }
                });

            } else {
                jQuery("[data-index='digital-river']").on('click',  function () {

                    if (!change_handler_added) {
                        var product_add_edit_page = jQuery("[data-index='dr_tax_group']").length !== 0 && jQuery("[data-index='dr_tax_type']").length !== 0;

                        if (product_add_edit_page) {

                            addSelectsEventHandlers("[data-index='dr_tax_group'] select", "[data-index='dr_tax_type'] select");

                            change_handler_added = 1;
                        }
                    }
                });
            }

            function addSelectsEventHandlers(dr_tax_group_select_selector, dr_tax_type_select_selector ) {

                var dr_tax_group_select = jQuery(dr_tax_group_select_selector);
                var dr_tax_type_select = jQuery(dr_tax_type_select_selector);

                jQuery('body').on('change', dr_tax_group_select_selector,  function () {

                    var dr_tax_group_selected_value = jQuery(this).find(":selected").val();
                    jQuery(dr_tax_type_select_selector + " option:gt(0)").remove();

                    if (dr_tax_group_selected_value !== '') {

                        jQuery.each(config.tax_values, function( index, value ) {
                            if (value.dr_tax_group === dr_tax_group_selected_value) {
                                dr_tax_type_select.append(jQuery("<option></option>")
                                    .attr("value", value.entity_id).text(value.dr_tax_type));
                            }
                        });
                        jQuery(dr_tax_type_select_selector + " option:eq(0)").attr('selected','selected').change();
                    }
                });
            }
        });
    }
});
