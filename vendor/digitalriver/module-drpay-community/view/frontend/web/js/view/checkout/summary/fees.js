/**
 *
 * Control to render value in Cart/Checkout for Duty Fee item
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
define(
    [
        'jquery',
        'ko',
        'underscore',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote'
    ],
    function ($, ko, _, Component, quote) {
        "use strict";

        return Component.extend({
            defaults: {
                template: 'Digitalriver_DrPay/checkout/summary/fees'
            },

            items: ko.observableArray([]),
            totals: quote.getTotals(),

            isDisplayedTotal: function () {
                return this.items().length > 0;
            },

            initialize: function () {
                this._super();
                quote.getTotals().subscribe(
                    $.proxy(function (totals) {
                        if (!totals) {
                            return;
                        }

                        var weeeItems = totals.items.filter(this.checkItemWeeeData);
                        if (!weeeItems || !weeeItems.length) {
                            this.items([]);
                            return;
                        }

                        this.items(
                            weeeItems.map(
                                $.proxy(function (item) {
                                    return {
                                        productName: item.name,
                                        fees: this.getItemWeeeData(item)
                                    };
                                }, this)
                            )
                        );
                    }, this)
                );

                return this;
            },

            getItemWeeeData: function (item) {
                try {
                    var items = JSON.parse(item.weee_tax_applied);
                    return items.map(
                        $.proxy(function (item) {
                            item.amount_formatted = this.getFormattedPrice(item.row_amount);
                            return item;
                        }, this)
                    )
                } catch {
                    return [];
                }
            },

            checkItemWeeeData: function (item) {
                var weeeData = item.weee_tax_applied;
                return !_.isEmpty(weeeData) && weeeData !== '[]';
            },

            getTitle: function () {
                return this.title;
            }
        });
    }
);
