/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_WebApplicationFirewall
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define([
    "jquery",
    "uiComponent"
],function ($, Component) {
    "use strict";

    return Component.extend({
        initialize: function (config) {
            var isAuthEnabled = config.isAuthEnabled;

            $(document).ready(function () {
                $("#two_step_auth").change(function () {
                    if (isAuthEnabled == $(this).val()) {
                        $("#save_btn_field").addClass('hide');
                    } else {
                        $("#save_btn_field").removeClass('hide');
                    }
                });
            });
        }
    });
});
