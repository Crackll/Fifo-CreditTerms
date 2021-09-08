/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'mage/template',
], function ($, $t, mageTemplate) {
    'use strict';
    $.widget('mage.hours', {
        _create: function () {
            var self = this;

            $(".business_days input").on("click",function () {
                if ($(this).is(':checked')) {
                    $(this).parent().addClass("active");
                    $(this).parents(".timerange").find(".duration").show();
                    $(this).parents(".timerange").find(".content select").each(function () {
                        $(this).removeAttr("disabled");
                    });
                } else {
                    $(this).parent().removeClass("active");
                    $(this).parents(".timerange").find(".duration").hide();
                    $(this).parents(".timerange").find(".content select").each(function () {
                        $(this).attr("disabled","disabled");
                    });
                }
            });

            $(document).ready(function () {
                $(".working-days .content select").each(function () {
                    var thisthis = $(this);
                    self.calculateHours(thisthis);
                });

            });

            $(".working-days .content select").on("change",function () {
                var thisthis = $(this);
                self.calculateHours(thisthis);
            });
        },

        calculateHours: function (thisthis) {
            var self = this;
            var valuestart = thisthis.parents(".content").find(".morning_time").val()+" "+thisthis.parents(".content").find(".morning_cycle").val();
            var valuestop = thisthis.parents(".content").find(".evening_time").val()+" "+thisthis.parents(".content").find(".evening_cycle").val();
            var timeStart = new Date(self.options.firstdate + valuestart).getHours();
            var timeEnd = new Date(self.options.firstdate + valuestop).getHours();
            var hourDiff = timeEnd - timeStart;

            var startMin = thisthis.parents(".content").find(".morning_time").val().split(':');
            var endMin = thisthis.parents(".content").find(".evening_time").val().split(':');
            var min = startMin[1] - endMin[1];
            if (min == 30) {
                hourDiff = hourDiff - 1;
            }
            if (min != 0) {
                thisthis.parents(".content").find(".duration .minutes-duration").show();
            } else {
                thisthis.parents(".content").find(".duration .minutes-duration").hide();
            }
            thisthis.parents(".content").find(".duration .hours-duration .hours-num").text(hourDiff);
        }
    });
    return $.mage.hours;
});
