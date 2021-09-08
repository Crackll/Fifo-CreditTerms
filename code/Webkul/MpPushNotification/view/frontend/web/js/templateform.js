/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
require (
    ['jquery',
    'jquery/validate',
    ],
    function($) {
        $.validator.addMethod(
            'validate-fileextensions', function (v, elm) {
                var extensions = ['jpeg', 'jpg', 'png', 'gif'];
                if (!v) {
                    return true;
                }
                with (elm) {
                    var ext = value.substring(value.lastIndexOf('.') + 1);
                    for (i = 0; i < extensions.length; i++) {
                        if (ext == extensions[i]) {
                            return true;
                        }
                    }
                }
                return false;
            }, $.mage.__('File type not allowed.')
        );

        $('#new-template').on('submit', function() {
            if ($('#new-template').valid()) {
                $('body').trigger('processStart');
            }
        });
    }
)