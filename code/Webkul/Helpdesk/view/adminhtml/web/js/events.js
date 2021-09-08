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
    'mage/adminhtml/wysiwyg/tiny_mce/setup',
    'mage/calendar'
], function ($, $t, mageTemplate, tiny_mce) {
    'use strict';
    $.widget('mage.events', {
        _create: function () {
            var self = this;
            if (parseInt(self.options.alloweditor)) {
                var config = self.options.wysiwygConfig,
                    editor;
                $.extend(config, {
                    settings: {
                        theme_advanced_buttons1 : 'bold,italic,|,justifyleft,justifycenter,justifyright,|,' +
                            'fontselect,fontsizeselect,|,forecolor,backcolor,|,link,unlink,image,|,bullist,numlist,|,code',
                        theme_advanced_buttons2: null,
                        theme_advanced_buttons3: null,
                        theme_advanced_buttons4: null,
                        theme_advanced_statusbar_location: null
                    },
                    files_browser_window_url: false
                });

                var editorMailAgent = new wysiwygSetup("mail_agent", {
                    "width":"99%",  // defined width of editor
                    "height":"200px", // height of editor
                    "plugins":[{"name":"image"}], // for image
                    "tinymce4":{"toolbar":"formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap","plugins":"advlist autolink lists link charmap media noneditable table contextmenu paste code help table",
                    }
                });

                editorMailAgent.setup("exact");

                var editorMailGroup = new wysiwygSetup("mail_group", {
                    "width":"99%",  // defined width of editor
                    "height":"200px", // height of editor
                    "plugins":[{"name":"image"}], // for image
                    "tinymce4":{"toolbar":"formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap","plugins":"advlist autolink lists link charmap media noneditable table contextmenu paste code help table",
                    }
                });

                editorMailGroup.setup("exact");

            
                var editorMailCustomer = new wysiwygSetup("mail_customer", {
                    "width":"99%",  // defined width of editor
                    "height":"200px", // height of editor
                    "plugins":[{"name":"image"}], // for image
                    "tinymce4":{"toolbar":"formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap","plugins":"advlist autolink lists link charmap media noneditable table contextmenu paste code help table",
                    }
                });

                editorMailCustomer.setup("exact");
            }

            if (document.getElementById("one_condition_date") != null) {
                $('#one_condition_date').datepicker();
            }
            if (document.getElementById("all_condition_date") != null) {
                $('#all_condition_date').datepicker();
            }

            $(".rule-action-container").delegate(".condition.action-type", "change", function () {
                var this_this = $(this);
                var flag = 0;
                $(this).parents("tr").siblings().find(".action-type").each(function () {
                    if ($(this).find("option:selected").val() == this_this.val()) {
                        flag = 1;
                    }
                });
                if (flag != 1) {
                    var context = {field_name:$(this).parents("table").attr("for")};
                    var progressTmpl = mageTemplate(".condition."+$(this).val()),tmpl;
                    tmpl = progressTmpl({
                        data: context
                    });
                    $(this).parent().next().html(tmpl);
                } else {
                    $(this).parents('tr').remove();
                }
                if (document.getElementById("one_condition_date") != null) {
                    $('#one_condition_date').datepicker();
                }
                if (document.getElementById("all_condition_date") != null) {
                    $('#all_condition_date').datepicker();
                }
            });

            $(".rule-action-container").delegate(".event.action-type", "change", function () {
                var this_this = $(this);
                var flag = 0;
                $(this).parents("tr").siblings().find(".action-type").each(function () {
                    if ($(this).find("option:selected").val() == this_this.val()) {
                        flag = 1;
                    }
                });
                if (flag != 1) {
                    var context = {field_name:$(this).parents("table").attr("for")};
                    var progressTmpl = mageTemplate(".event."+$(this).val()),tmpl;
                    tmpl = progressTmpl({
                        data: context
                    });
                    $(this).parent().next().html(tmpl);
                } else {
                    $(this).parents('tr').remove();
                }
            });

            $(".add-action-btn").on("click", function () {
                var context = {field_name:$(this).parents("table").attr("for")};
                var progressTmpl = mageTemplate("."+$(this).attr("for")),tmpl;
                tmpl = progressTmpl({
                    data: context
                });
                $(this).parents(".rule-action-details").find("tbody.body_content").append(tmpl);
            });

            $(".rule-action-container").delegate(".delete-select-row","click",function () {
                $(this).parents('tr').remove();
            });

            $(".rule-action-container").delegate(".action.action-type", "change", function () {
                var this_this = $(this);
                var flag = 0;
                $(this).parents("tr").siblings("tr").find(".action-type").each(function () {
                    if ($(this).find("option:selected").val() == this_this.val()) {
                        flag = 1;
                    }
                });
                if (flag != 1) {
                    if ($(this).val() != "delete_ticket" && $(this).val() != "mark_spam") {
                        var progressTmpl = mageTemplate(".action."+$(this).val()),tmpl;
                        $(this).parent().next().html(progressTmpl);
                    } else {
                        $(this).parent().next().html("");
                    }
                    if ($(this).val() == "mail_agent" || $(this).val() == "mail_group" || $(this).val() == "mail_customer") {
                        var textareaId = $(this).val();
                        if (parseInt(self.options.alloweditor)) {
                            var editor = new wysiwygSetup(textareaId, {
                                "width":"99%",  // defined width of editor
                                "height":"200px", // height of editor
                                "plugins":[{"name":"image"}], // for image
                                "tinymce4":{"toolbar":"formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap","plugins":"advlist autolink lists link charmap media noneditable table contextmenu paste code help table",
                                }
                            });
            
                            editor.setup("exact");

                            $('#'+textareaId).addClass('wysiwyg-editor').data(
                                'wysiwygEditor',
                                editor
                            );
                        }
                    }
                } else {
                    $(this).parents('tr').remove();
                }
            });

            $(".rule-action-container").delegate(".mail_row input","keydown keypress click",function (ed) {
                var currentEditor = $(this).attr("id");
                var position = self.getCursorPosition();
            });

            $("#import-variables").on("click",function () {
                $("#overlay_modal").show();
                $("#variables-chooser").show();
                $('#variables-chooser').draggable({
                    cursor: "move",
                    handle: '#variables-chooser_top'
                });
                self.popalign();
            });

            $(window).resize(function () {
                self.popalign();
            });

            $("#variables-chooser_close").on("click",function () {
                $("#overlay_modal").hide();
                $("#variables-chooser").hide();
            });

            $("#variables-chooser a").on("click",function (e) {
                e.preventDefault();
                if (currentEditor == "mail_customer_subject" || currentEditor == "mail_agent_subject" || currentEditor == "mail_group_subject") {
                    var content = $("#"+currentEditor).val();
                    var output = [content.slice(0, position), $(this).attr("href"), content.slice(position)].join('');
                    $("#"+currentEditor).val(output);
                } else {
                    var content = tiny_mce.get(currentEditor).getContent();
                    var output = [content.slice(0, position), $(this).attr("href"), content.slice(position)].join('');
                    tiny_mce.get(currentEditor).setContent(output);
                }
                $("#overlay_modal").hide();
                $("#variables-chooser").hide();
            });

        },

        getCursorPosition : function () {
            var el = $(this).get(0);
            var pos = 0;
            if ('selectionStart' in el) {
                pos = el.selectionStart;
            } else if ('selection' in document) {
                el.focus();
                var Sel = document.selection.createRange();
                var SelLength = document.selection.createRange().text.length;
                Sel.moveStart('character', -el.value.length);
                pos = Sel.text.length - SelLength;
            }
            return pos;
        },

       popalign : function () {
           var winH = $(window).height();
           var winW = $(window).width();
           $("#variables-chooser").css('top', winH / 2 - $("#variables-chooser").height() / 2);
           $("#variables-chooser").css('left', winW / 2 - $("#variables-chooser").width() / 2);
       }
    });
    return $.mage.events;
});
