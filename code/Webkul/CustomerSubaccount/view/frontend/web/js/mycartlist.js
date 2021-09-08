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
    'Magento_Ui/js/modal/alert',
    "Magento_Ui/js/modal/modal",
    'mage/translate'
], function ($, alert, modal, $t) {
    'use strict';
    return function (option) {
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: false,
            // width:'200px',
            modalClass:"wkc-view-cart-popup"
        };

        $('td.view a').on('click', function(e){
            e.preventDefault();
            var url = $(this).attr('href');
            $.ajax({
                type: "GET",
                url: url,
                data: {},
                beforeSend: function() {
                    $('body').trigger("processStart");
                },
                success: function (response) {
                    $('body').trigger("processStop");
                    var cont='';
                    cont = $('<div />').append($(response).find('.wkc-cart-data-container'));
                    modal(options, cont);
                    cont.modal('openModal');
                },
                error: function (response) {
                    $('body').trigger("processStop");
                    alert({
                        content: '<div class="">'+$t('Something went wrong.')+'</div>'
                    });
                }
            })
        })
    }
});
    
