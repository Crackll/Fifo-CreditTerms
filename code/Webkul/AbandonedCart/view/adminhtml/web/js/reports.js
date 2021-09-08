/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AbandonedCart
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    'Webkul_AbandonedCart/js/Chart.bundle',
    'Webkul_AbandonedCart/js/utils',
    "mage/calendar",
    "mage/translate"
], function ($) {

    'use strict';
    return function (config) {
        var MONTHS = [];
        var color = Chart.helpers.color;
        var dataSets = [{
            label: $.mage.__('Abandoned Cart'),
            backgroundColor: color(window.chartColors.red).alpha(0.5).rgbString(),
            borderColor: window.chartColors.red,
            borderWidth: 1,
            data: config.abandonedCartData
        }, {
            label: $.mage.__('Mails Sent'),
            backgroundColor: color(window.chartColors.blue).alpha(0.5).rgbString(),
            borderColor: window.chartColors.blue,
            borderWidth: 1,
            data: config.sentMailsData
        }, {
            label: $.mage.__('Carts Recovered'),
            backgroundColor: color(window.chartColors.green).alpha(0.5).rgbString(),
            borderColor: window.chartColors.blue,
            borderWidth: 1,
            data: config.convertedData
        }];
        var barChartData = {
            labels: config.labels,
            datasets: dataSets
        };

        $(document).ready(function() {
            var ctx = document.getElementById('canvas').getContext('2d');
            window.myBar = new Chart(ctx, {
                type: 'bar',
                data: barChartData,
                options: {
                    responsive: true,
                }
            });

        });

        $("#calendar_inputField").calendar({
            changeYear: true,
            changeMonth: true,
            dateFormat :'yyyy/mm/dd',
            yearRange: "1970:2050",
            buttonText: "Select Date",
        });
        $("#calendar_inputField2").calendar({
            changeYear: true,
            changeMonth: true,
            dateFormat :'yyyy/mm/dd',
            yearRange: "1970:2050",
            buttonText: "Select Date",
        });
    }
});