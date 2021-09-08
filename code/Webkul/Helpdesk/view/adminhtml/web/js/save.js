/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
require([
    "jquery",
    'mage/translate',
    'mage/template',
], function ($, $t, mageTemplate) {
    'use strict';
    $('.save').on('click',function () {
        var id;
        var val;
        id = $('.form-inline').find('form').attr('id')
        val =   $('#'+id).valid();
        if (val == true) {
            $('body').trigger('processStart');
        }
        
    })
    return $.mage.dashboard;
});
