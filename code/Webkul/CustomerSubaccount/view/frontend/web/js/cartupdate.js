/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomerSubaccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define([
    "jquery",
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';
    return function (option) {
        var sections = ['cart'];
        if (option.newcustomer) {
            sections.push('customer');
        }
        customerData.invalidate(sections);
        customerData.reload(sections, true);
    }
});
