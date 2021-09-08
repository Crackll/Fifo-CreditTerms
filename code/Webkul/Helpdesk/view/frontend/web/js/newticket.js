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
    'mage/adminhtml/wysiwyg/tiny_mce/setup'
], function ($, $t, mageTemplate, tiny_mce) {
    'use strict';
    $.widget('mage.newticket', {
        _create: function () {
            var self = this;
            if (parseInt(self.options.alloweditor)) {
                var wysiwygcompany_description = new wysiwygSetup("query", {
                    "width":"99%",  // defined width of editor
                    "height":"200px", // height of editor
                    "plugins":[{"name":"image"}], // for image
                    "tinymce4":{"toolbar":"formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap","plugins":"advlist autolink lists link charmap media noneditable table contextmenu paste code help table",
                    }
                });
                wysiwygcompany_description.setup("exact");
            }

            $(".hide_attribute").parents("li").hide();
            $("#help_type").on("change",function () {
                var this_this = $(this);
               $(".custom_attribute select ,.custom_attribute input ,.custom_attribute textarea") .each(function () {
                    if ($(this).attr("for") != "") {
                        if (this_this.val() == $(this).attr("for")) {
                            $(this).parents("li").show();
                            $(this).addClass($(this).attr("data-hide-validation"));
                        } else {
                            $(this).parents("li").hide();
                            $(this).removeClass($(this).attr("data-hide-validation"));
                        }
                    }
               });
            });

            var allowedExtension = self.options.allowedextensions;
            $('#wk_ts_file').on('change',function (e) {
                var this_this = $(this);
                if ($(this).get(0).files.length > parseInt(1)) {
                    e.stopImmediatePropagation();
                    $(this).val('');
                    alert($t("Can not upload more than "+1+" images"));
                } else {
                    var flag = 0;
                    var files = e.originalEvent.target.files;
                    for (var i = 0, len = files.length; i < len; i++) {
                        var extension = files[i].name.substr((files[i].name.lastIndexOf('.') +1));
                        if (allowedExtension.indexOf(extension) < 0) {
                            flag = 1;
                        }
                    }
                    if (flag) {
                        this_this.val('');
                        alert($t("File type not allowed,Allowed file: "+allowedExtension));
                    }
                }
            });
        }
    });
    return $.mage.newticket;
});
