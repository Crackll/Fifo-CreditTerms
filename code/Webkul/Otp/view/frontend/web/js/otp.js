require([
    'jquery'
], function ($) {
    // 'use strict';

        $(".mbi.mbi-user").click(function(){
            setTimeout(function(){ 
                $('.mfp-wrap.mfp-close-btn-in.mfp-auto-cursor').removeAttr("tabindex"); 
            }, 3000);
        })
});
