/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

/*jshint jquery:true*/
require(
    [
    'jquery'
    ],
    function ($) {
    'use strict';
        setTimeout(function(){
        if($("input[name*='pushnotification_template[logo]']").length > 0){
            $("input[name*='pushnotification_template[logo]']").attr('name','logo');
            return false;
        }
    }, 3000);
    $(".save").click(function(){
        setTimeout(function(){
        if($(".admin__field-error").length == 0){
            $(".save").attr('disabled','disabled');
        }
        return false;
    }, 300);

    });
    }
);