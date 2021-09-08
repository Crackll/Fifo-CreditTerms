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
    function readURL(input) {
        if (input.files && input.files[0]) {
          var reader = new FileReader();
          reader.onload = function(e) {
            $('#imgsrc').attr('src', e.target.result);
            $('#imgsrc').attr('style', 'display:block;');
          }
          
          reader.readAsDataURL(input.files[0]);
        }
      }
      
      $("#logo").change(function() {
        readURL(this);
      });
    }
);