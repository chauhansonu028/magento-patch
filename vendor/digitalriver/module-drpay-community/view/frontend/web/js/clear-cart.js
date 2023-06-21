/**
 *
 * Manually clears cart private content cache section from the frontend
 *
 * @summary
 * Invaldates cart private content cache section
 * @author   Vujadin Scepanovic <vujadin.scepanovic@rs.ey.com>
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
define([
    'Magento_Customer/js/customer-data'
], function (customerData) {
    'use strict';

    return function () {
        var sections = ['cart'];
        customerData.invalidate(sections);
        customerData.reload(sections, true);
    };
});
