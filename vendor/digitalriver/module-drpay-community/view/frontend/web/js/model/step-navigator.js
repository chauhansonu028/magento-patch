/**
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

/**
 * @api
 */
define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Digitalriver_DrPay/js/view/checkout/action/manage-tax-ids',
        'Digitalriver_DrPay/js/view/checkout/action/manage-invoice-attribute'
    ], function ($, ko, quote, manageTaxIds, manageInvoiceAttribute) {
        'use strict';

        var dr_enabled = window.checkoutConfig.payment.drpay_dropin.is_active;
        var steps = ko.observableArray();

        return {
            dr_enabled: dr_enabled,
            steps: steps,
            stepCodes: [],
            validCodes: [],

            /**
             * @return {Boolean}
             */
            handleHash: function () {
                if (this.dr_enabled) {
                    var hashString = window.location.hash.replace('#', ''),
                        isRequestedStepVisible;
    
                    if (hashString === '') {
                        return false;
                    }
    
                    if ($.inArray(hashString, this.validCodes) === -1) {
                        window.location.href = window.checkoutConfig.pageNotFoundUrl;
    
                        return false;
                    }
    
                    isRequestedStepVisible = steps.sort(this.sortItems).some(
                        function (element) {
                            return (element.code == hashString || element.alias == hashString) && element.isVisible(); //eslint-disable-line
                        }
                    );
    
                    //if requested step is visible, then we don't need to load step data from server
                    if (isRequestedStepVisible) {
                        return false;
                    }
    
                    steps().sort(this.sortItems).forEach(
                        function (element) {
                            if (element.code == hashString || element.alias == hashString) { //eslint-disable-line eqeqeq
                                element.navigate(element);
                            } else {
                                element.isVisible(false);
                            }
    
                        }
                    );
                }

                return false;
            },

            /**
             * @param {String} code
             * @param {*} alias
             * @param {*} title
             * @param {Function} isVisible
             * @param {*} navigate
             * @param {*} sortOrder
             */
            registerStep: function (code, alias, title, isVisible, navigate, sortOrder) {
                var hash, active;

                if ($.inArray(code, this.validCodes) !== -1) {
                    throw new DOMException('Step code [' + code + '] already registered in step navigator');
                }

                if (alias != null) {
                    if ($.inArray(alias, this.validCodes) !== -1) {
                        throw new DOMException('Step code [' + alias + '] already registered in step navigator');
                    }
                    this.validCodes.push(alias);
                }
                this.validCodes.push(code);
                steps.push(
                    {
                        code: code,
                        alias: alias != null ? alias : code,
                        title: title,
                        isVisible: isVisible,
                        navigate: navigate,
                        sortOrder: sortOrder
                    }
                );
                active = this.getActiveItemIndex();
                steps.each(
                    function (elem, index) {
                        if (active !== index) {
                            elem.isVisible(false);
                        }
                    }
                );
                this.stepCodes.push(code);
                hash = window.location.hash.replace('#', '');

                if (hash != '' && hash != code) { //eslint-disable-line eqeqeq
                    //Force hiding of not active step
                    isVisible(false);
                }
            },

            /**
             * @param  {Object} itemOne
             * @param  {Object} itemTwo
             * @return {Number}
             */
            sortItems: function (itemOne, itemTwo) {
                return itemOne.sortOrder > itemTwo.sortOrder ? 1 : -1;
            },

            /**
             * @return {Number}
             */
            getActiveItemIndex: function () {
                var activeIndex = 0;

                steps().sort(this.sortItems).some(
                    function (element, index) {
                        if (element.isVisible()) {
                            activeIndex = index;

                            return true;
                        }

                        return false;
                    }
                );

                return activeIndex;
            },

            /**
             * @param  {*} code
             * @return {Boolean}
             */
            isProcessed: function (code) {
                var activeItemIndex = this.getActiveItemIndex(),
                    sortedItems = steps().sort(this.sortItems),
                    requestedItemIndex = -1;

                sortedItems.forEach(
                    function (element, index) {
                        if (element.code == code) { //eslint-disable-line eqeqeq
                            requestedItemIndex = index;
                        }
                    }
                );

                return activeItemIndex > requestedItemIndex;
            },

            /**
             * @param {*} code
             * @param {*} scrollToElementId
             */
            navigateTo: function (code, scrollToElementId) {
                var sortedItems = steps().sort(this.sortItems),
                    bodyElem = $('body');

                scrollToElementId = scrollToElementId || null;

                if (!this.isProcessed(code)) {
                    return;
                }
                sortedItems.forEach(
                    function (element) {
                        if (element.code == code) { //eslint-disable-line eqeqeq
                            element.isVisible(true);
                            bodyElem.animate(
                                {
                                    scrollTop: $('#' + code).offset().top
                                }, 0, function () {
                                    window.location = window.checkoutConfig.checkoutUrl + '#' + code;
                                }
                            );

                            if (scrollToElementId && $('#' + scrollToElementId).length) {
                                bodyElem.animate(
                                    {
                                        scrollTop: $('#' + scrollToElementId).offset().top
                                    }, 0
                                );
                            }
                        } else {
                            element.isVisible(false);
                        }

                    }
                );

                // Hide terms.
                if (this.dr_enabled) {
                    if(code == 'shipping') {
                        $(".payment-method._active .payment-method-content .payment-edit").click();
                        manageTaxIds.deleteTaxIds();
                        manageInvoiceAttribute.deleteInvoiceAttribute();
                    }
    
                    if(code == 'billing' && quote.isVirtual()) {
                        manageTaxIds.deleteTaxIds();
                        manageInvoiceAttribute.deleteInvoiceAttribute();
                    }
                }
            },

            /**
             * Sets window location hash.
             *
             * @param {String} hash
             */
            setHash: function (hash) {
                window.location.hash = hash;
            },

            /**
             * Next step.
             */
            next: function () {
                if (this.dr_enabled) {
                    // setTimeout(function(){},4000); was used to wrap this because of loading delay.  Keep this comment until we are sure that this is not needed after full test.
                    if (jQuery('#billing-address-same-as-shipping-drpay_dropin').length > 0) {
                        // force drop in reload to get latest address
                        window.checkoutConfig.payment.drpay_dropin.payment_session_id = null;
                        loadDropIn(true);
                    }
                }
                
                var activeIndex = 0,
                    code;
                steps().sort(this.sortItems).forEach(
                    function (element, index) {
                        if (element.isVisible()) {
                            element.isVisible(false);
                            activeIndex = index;
                        }
                    }
                );

                if (steps().length > activeIndex + 1) {
                    code = steps()[activeIndex + 1].code;
                    steps()[activeIndex + 1].isVisible(true);
                    this.setHash(code);
                    document.body.scrollTop = document.documentElement.scrollTop = 0;
                }
            }
        };
    }
);
