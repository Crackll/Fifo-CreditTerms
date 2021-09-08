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
    'mage/translate'
], function ($, $t) {
    'use strict';
    return function (option) {
        $('.reviews-actions a.add').remove();
        $('div.review-add').remove();
    }
})