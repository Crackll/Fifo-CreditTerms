/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AccordionFaq
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define([
    "jquery",
    'jquery/ui'
], function ($,ui) {
    'use strict';
    $.widget('mage.wk_accordionfaq', {
        _create: function () {
            var self = this;
            var groupCode=self.options.group;
            
            $(".accordion"+groupCode).accordion({
                autoHeight:false,
                animated: 'bounceslide',
                collapsible: true,
                heightStyle: "content"
            });
            $(".wk_accordion").show();
        },
    });
    return $.mage.wk_accordionfaq;
});