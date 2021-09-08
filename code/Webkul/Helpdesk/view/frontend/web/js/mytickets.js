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
    $.widget('mage.mytickets', {
        _create: function () {
            var self = this;
            $(".wk_ts_tickets_container").delegate(".mydropdown","click",function (event) {
                event.stopPropagation();
                $(".dropdown-menu").hide();
                $(this).next().show();
                $(this).find(".dropdown-menu").show();
            });

            $(".wk_ts_tickets_container").delegate(".dropdown-menu li,.wk_ts_clear","click",function () {
                var this_this = $(this);
                $(".ticketsLoader").show();
                $.ajax({
                    url : this_this.attr("data-href"),
                    type : 'GET',
                    data : {currnetpage : this_this.attr('data-next-page')},
                    dataType : 'json',
                    success : function (content) {
                        $(".wk_ts_tickets_container .wk_ts_content").html($(content.listing).find(".container_ajax").html());
                        $(".ticketsLoader").hide();
                    }
                });
            });

            $('.wk_ts_tickets_container').delegate(".wk_ts_mark_all","click",function (event) {
                if (this.checked) {
                    $('.tscheckbox').each(function () {
                        this.checked = true;
                    });
                    $('.wk_ts_delete').removeClass("wk_disabled");
                } else {
                    $('.tscheckbox').each(function () {
                        this.checked = false;
                    });
                    $('.wk_ts_delete').addClass("wk_disabled");
                }
            });

            $('.wk_ts_tickets_container').delegate(".tscheckbox","click",function (event) {
                var flag = 0;
                $('.tscheckbox').each(function () {
                    if ($(this).is(":checked")) {
                        flag = 1;
                    }
                });
                if (flag) {
                    $('.wk_ts_delete').removeClass("wk_disabled");
                } else {
$('.wk_ts_delete').addClass("wk_disabled");
                }
            });

            $('.wk_ts_tickets_container').delegate(".wk_ts_delete","click",function (e) {
                var flag =0;
                $('.tscheckbox').each(function () {
                    if (this.checked == true) {
                        flag =1;
                    }
                });
                if (flag == 0) {
                    alert($t("No Checkbox is checked"));
                    return false;
                } else {
                    var dicisionapp = confirm($t("Are you sure you want to delete these ticket(s) ?"));
                    if (dicisionapp == true) {
                        $('#formmassdelete').submit();
                    } else {
                        return false;
                    }
                }
            });
        }
    });
    return $.mage.mytickets;
});
