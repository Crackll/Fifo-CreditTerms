/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpTimeDelivery
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 /*jshint jquery:true*/
define(
    [
    "jquery",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    "jquery/ui",
    'mage/calendar',
    'jquery/jquery-ui-timepicker-addon'
    ],
    function ($, $t, alert) {
        'use strict';
        $.widget(
            'mage.grid',
            {
                _create: function () {
                    var self = this;
                    var startTimeTextBox = $('#special-from-time');
                    var endTimeTextBox = $('#special-to-time');
                    startTimeTextBox.timepicker(
                        {
                            timeFormat: 'hh:mm TT',
                            controlType: 'select',
                        }
                    );
                    endTimeTextBox.timepicker(
                        {
                            timeFormat: 'hh:mm TT',
                            controlType: 'select',
                        }
                    );

                    $(".wk-mp-body").dateRange({
                        'dateFormat':'mm/dd/yy',
                        'from': {
                            'id': 'special-from-date'
                        },
                        'to': {
                            'id': 'special-to-date'
                        }
                    });
                }

            }
        );
        return $.mage.grid;
    }
);
