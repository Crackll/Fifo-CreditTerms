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
], function ($, $t, mageTemplate, tinymce) {
    'use strict';
    $.widget('mage.responses', {
        _create: function () {
            var self = this;
            var config = self.options.wysiwygConfig,
                editor,
                currentEditor,
                position,
                textareaId;
            var editor = "";
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
            })

            editorMailGroup.setup("exact");
           
            var editorMailCustomer = new wysiwygSetup("mail_customer", {
                "width":"99%",  // defined width of editor
                "height":"200px", // height of editor
                "plugins":[{"name":"image"}], // for image
                "tinymce4":{"toolbar":"formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap","plugins":"advlist autolink lists link charmap media noneditable table contextmenu paste code help table",
                }
            })

            editorMailCustomer.setup("exact");

            $(document).ready(function(){
                var action = $(".action-type option:selected").val()
                if(action == "mail_group"){
                    editor = editorMailGroup
                }else if(action == "mail_customer"){
                    editor = editorMailCustomer
                }else if(action == "mail_agent"){
                    editor = editorMailAgent
                }
            })

            $(".add-action-btn").on("click",function () {
                var progressTmpl = mageTemplate($(".response_row").html());
                $(this).parents(".response-action-details").find("tbody.body_content").append(progressTmpl);
            });

            $(".response-action-container").delegate(".delete-select-row","click",function () {
                $(this).parents('tr').remove();
            });

            $(".response-action-container").delegate(".action-type","change",function () {
                var this_this = $(this);
                var flag = 0;
                $(this).parents("tr").siblings("tr").find(".action-type").each(function () {
                    if ($(this).find("option:selected").val() == this_this.val()) {
                        flag = 1;
                    }
                });

                if (flag != 1) {
                    if ($(this).val() != "delete_ticket" && $(this).val() != "mark_spam") {
                        var progressTmpl = mageTemplate("."+$(this).val());
                        $(this).parent().next().html(progressTmpl);
                    } else {
                        $(this).parent().next().html("");
                    }
                    if ($(this).val() == "mail_agent" || $(this).val() == "mail_group" || $(this).val() == "mail_customer") {
                        $("#import-variables").css('display','block');
                        textareaId = $(this).val();
                        editor = new wysiwygSetup(`${textareaId}`, {
                            "width":"99%",  // defined width of editor
                            "height":"200px", // height of editor
                            "plugins":[{"name":"image"}], // for image
                            "tinymce4":{"toolbar":"formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap","plugins":"advlist autolink lists link charmap media noneditable table contextmenu paste code help table",
                            }
                        })
                        editor.setup("exact");

                        currentEditor = editor.id;
                    }
                } else {
                    $(this).parents('tr').remove();
                }
            });

            $(".response-action-container").delegate(".mail_row input ","keydown keypress click",function (ed) {
                currentEditor = $(this).attr("id");
                position = 0;
                if ('selectionStart' in $(this)) {
                    position = $(this).selectionStart;
                } else if ('selection' in document) {
                    $(this).focus();
                    var Sel = document.selection.createRange();
                    var SelLength = document.selection.createRange().text.length;
                    Sel.moveStart('character', -$(this).value.length);
                    position = Sel.text.length - SelLength;
                }
            });

            $("#import-variables").on("click",function () {
                var self = this;
                $("#overlay_modal").show();
                $("#variables-chooser").show();
                $('#variables-chooser').draggable({
                    cursor: "move",
                    handle: '#variables-chooser_top'
                });
                var winH = $(window).height();
                var winW = $(window).width();
                $("#variables-chooser").css('left', winW / 2 - $("#variables-chooser").width() / 2);
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
                    var content = "";
                    console.log(editorMailCustomer);
                    console.log(editorMailGroup);
                    console.log(currentEditor);
                    if(editor.wysiwygInstance.getContent()){
                        content = editor.wysiwygInstance.getContent();
                    }
                    var output = [$(this).attr("href"), content.slice(position)].join('');
                    editor.wysiwygInstance.setContent(output);
                }
                $("#overlay_modal").hide();
                $("#variables-chooser").hide();
            });

        }
    });
    return $.mage.responses;
});
