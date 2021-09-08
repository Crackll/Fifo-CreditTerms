require([
    "jquery"
  ], 
  function($) {
    // "use strict";
    $('#country').on('change',function(){
        if ($('#region_id').prop('disabled') == true) {
            $('.region_id').removeAttr("disabled");
        }
    });
});