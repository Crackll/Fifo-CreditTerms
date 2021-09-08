/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'mage/template',
], function ($, $t, mageTemplate) {
    'use strict';
    $.widget('mage.navigation', {
        _create: function () {
            var self = this;
            $(".ticket_search").on("click",function (event) {
                event.stopPropagation();
                $(this).siblings(".dropdown-menu").toggle();
            });

            $(".search_information a").on("click",function (e) {
                e.preventDefault();
                var val = $(this).text().trim();
                $(".ticket_search").val(val);
            });

            $(".search_information .ticket_search").on("input",function () {
                var this_this = $(this);
                var flag = 0;
                $(".dropdown-menu.inforrmation").find("li").each(function () {
                    var text = $(this).find("a").text();
                    var n = text.search(this_this.val());
                    if (n < 0) {
                        $(this).hide();
                    } else {
                        $(this).show();
                        flag = 1;
                    }
                });
                if (flag == 0) {
                    $(".dropdown-menu.inforrmation").find(".no-results").show();
                } else {
                    $(".dropdown-menu.inforrmation").find(".no-results").hide();
                }
            });

            $(".dropdown").on("click",function (event) {
                event.stopPropagation();
                $(".dropdown-menu").hide();
                $(this).find(".dropdown-menu").toggle();
            });

            $(document).on("click",function (event) {
                event.stopPropagation();
                var className = event.target.className;
                if (className!='dropdown-menu') {
                    $(".dropdown-menu").hide();
                }
            });

            $(".wk_search_btn").on("click",function () {
                $(".dropdown-menu.inforrmation").find("li").each(function () {
                    var text = $(this).find("a").text();
                    if (text != "" && $(".ticket_search").val() == text) {
                        window.location = $(this).find("a").attr("href");
                    }
                });
            });

            // js for skip navigation
            $(".wk_ts_skip_nav_header i").on("click",function () {
                $(".wk_dropdown_nav").slideToggle();
            });

            $(".wk_dropdown_nav i.fa-plus, i.fa-minus").on("click",function () {
                $(this).parent().siblings().find(".wk_dropdown_list").hide();
                $(this).parent().siblings().removeClass("active").attr("data-flag","0")
                $(this).parent().siblings().find("i.fa-minus").removeClass("fa-minus").addClass("fa-plus");
                $(this).next().slideToggle();
                if ($(this).parent().attr("data-flag") == "0") {
                    $(this).parent().addClass("active");
                    $(this).parent().attr("data-flag","1");
                    $(this).addClass("fa-minus");
                    $(this).removeClass("fa-plus");
                } else {
                    $(this).parent().removeClass("active");
                    $(this).parent().attr("data-flag","0");
                    $(this).removeClass("fa-minus");
                    $(this).addClass("fa-plus");
                }
            });
        }
    });
    return $.mage.navigation;
});
