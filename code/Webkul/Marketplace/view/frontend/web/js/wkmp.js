require([
    'jquery'
], function ($) {
    'use strict';

    // var window_size = window.matchMedia('(max-width: 768px)');

    if ($(window).width() >= 786) {  
        $(".wk-mp-page-wrapper").attr('style', 'width: calc(100% - 200px) !important');
    }

    $(window).resize(function(){
        if ($(window).width() >= 786) {  
            $(".wk-mp-page-wrapper").attr('style', 'width: calc(100% - 200px) !important');
        } else {
            $(".wk-mp-page-wrapper").attr('style', 'width: calc(100% - 0px) !important');
        }  
    });
    
});
