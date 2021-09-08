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
], function ($, $t, mageTemplate) {
    'use strict';
    $.widget('mage.viewticket', {
        _create: function () {
            var self = this;

            var wysiwygcompany_description = new wysiwygSetup("wk_reply_text_field", {
                "width":"99%",  // defined width of editor
                "height":"200px", // height of editor
                "plugins":[{"name":"image"}], // for image
                "tinymce4":{"toolbar":"formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap","plugins":"advlist autolink lists link charmap media noneditable table contextmenu paste code help table",
                }
            });
            if (parseInt(self.options.alloweditor)) {
                wysiwygcompany_description.setup("exact");
            }

            var remianingThread = parseInt(self.options.remianingThread);
            var threadLimit = parseInt(self.options.threadLimit);
            var totalThread = parseInt(self.options.totalThread);
            $(".msg_submit_btn").on("click",function (e) {
                e.preventDefault();
                var textareaId = $(this).parents("ul").find("textarea").attr("id");
                if (parseInt(self.options.alloweditor)) {
                    if (tinymce.get(textareaId).getContent() != "") {
                        if (reply_form ==  true) {
                            $(this).parents("form").submit();
                        }
                    } else {
                        $(".required-field-error-msg").show();
                        setTimeout(function () {
                            $(".required-field-error-msg").fadeOut();
                        }, 3000);
                    }
                } else {
                    if ($("#wk_reply_text_field").val() != "") {
                        if (reply_form ==  true) {
                            $(this).parents("form").submit();
                        }
                    } else {
                        $('body').find(".required-field-error-msg").show();
                        setTimeout(function () {
                            $('body').find(".required-field-error-msg").fadeOut();
                        }, 3000);
                    }
                }
            });
            

            $(".wk_ts_more_expand_link").on("click",function () {
                var this_this = $(this);
                var count = parseInt(this_this.attr('data-next-page'));
                var maxPages = parseInt(this_this.attr('data-max-pages'));
                if (count <= maxPages) {
                    this_this.attr("disabled","disabled");
                    this_this.html("<i class='fa fa-spin fa-spinner'></i>");
                    $.ajax({
                        url : self.options.expandthreadsUrl,
                        type : 'GET',
                        data : {currnetpage : this_this.attr('data-next-page'),id : self.options.ticketId},
                        dataType : 'json',
                        success : function (content) {
                            remianingThread = remianingThread - threadLimit;
                            count = count + 1;
                            this_this.attr('data-next-page',count);
                            var html = "";
                            $(content.listing).find(".wk_ts_thread_container_box .wk_ts_one_thread").each(function () {
                                html = html + $(this)[0].outerHTML;
                            });
                            $(".wk_ts_thread_container_box").prepend(html);
                            if (remianingThread > 0) {
                                $(".wk_ts_more_expand_link").html("<span class='thread_count'>"+remianingThread+"</span>"+$t(' More Expand')+"<i class='fa fa-chevron-down'></i>");
                            } else {
                                $(".wk_ts_more_expand_link").html($t('Displayed All')+"<i class='fa fa-chevron-down'></i>");
                            }
                            this_this.removeAttr("disabled");
                        }
                    });
                }
            });

            $(".wk_close_msg").on("click",function () {
                $(this).parent().fadeOut();
            });

            $('.action_button.reply , .action_button.forward,.action_button.add_note').on('click', function () {
                var divId = $(this).attr('href');
                $('li[for='+divId+']').trigger('click');
                $('html,body').animate({
                    scrollTop: $(divId).offset().top
                },'slow');
            });

            setInterval(function () {
                saveInDraft();
            }, parseInt(self.options.draftsavetime));

            function saveInDraft()
            {
                var fieldType = "reply";
                if (parseInt(self.options.alloweditor)) {
                    var content = tinymce.get('wk_reply_text_field').getContent();
                } else {
                    var content = $("#wk_reply_text_field").val();
                }
                if (content != "") {
                    $.ajax({
                        url : self.options.saveindraftUrl,
                        type : 'POST',
                        data : {form_key: self.options.formkey,field:fieldType,id : self.options.ticketId,content:content},
                        dataType : 'json',
                        success : function (content) {
                            $(".ticketsLoader").hide();
                            $(".ajax-draft-save-msg").show();
                            setTimeout(function () {
                                $(".ajax-draft-save-msg").fadeOut();
                            }, 3000);
                            $("#draft_msg_box").text("<?php echo __('Draft Saved'); ?>");
                        }
                    });
                }
            }

            $(".wk_ts_attachment_block").on("mouseover",function () {
                $(this).find("a").show();
            });

            $(".wk_ts_attachment_block").on("mouseout",function () {
                $(this).find("a").hide();
            });

            $(".wk_ts_thread_container").delegate(".wk_ts_one_thread_body","mouseover",function () {
                $(this).find(".deletethread").show();
            });

            $(".wk_ts_thread_container").delegate(".wk_ts_one_thread_body","mouseout",function () {
                $(this).find(".deletethread").hide();
            });

            $(".wk_ts_thread_container").delegate(".deletethread","click",function () {
                var this_this = $(this);
                var id = $(this).attr("data-id");
                var dicisionapp = confirm($t(" Are you sure you want to delete this thread ? "));
                if (dicisionapp == true) {
                    $(".ticketsLoader").show();
                    $.ajax({
                        url : self.options.deletethreadUrl,
                        type : 'GET',
                        data : {id :id},
                        dataType : 'json',
                        success : function (content) {
                            $(".ticketsLoader").hide();
                            this_this.parents(".wk_ts_one_thread").remove();
                            $(".ajax-success-msg .msg").text($t('Success ! you have been successfully deleted thread.'));
                            $(".ajax-success-msg").show();
                            setTimeout(function () {
                                $(".ajax-success-msg").fadeOut();
                            }, 3000);
                        }
                    });
                }
            });

            $('.action_button.delete').on("click",function () {
                var id = self.options.ticketId;
                var dicisionapp = confirm($t(" Are you sure you want to delete this ticket ? "));
                if (dicisionapp == true) {
                    window.location = self.options.ticketdeleteUrl.concat("id/",id);
                }
            });

            var allowedExtension = self.options.allowedextensions;
            $('#wk_ts_file').on('change',function (e) {
                // Fix: Previously type was not defined
                var this_this = $(this);
                if ($(this).get(0).files.length > parseInt(self.options.numAllowFile)) {
                    e.stopImmediatePropagation();
                    $(this).val('');
                    alert($t('Can not upload more than '+self.options.numAllowFile+' images.'));
                } else {
                    var flag = 0;
                    var files = e.originalEvent.target.files;
                    for (var i = 0, len = files.length; i < len; i++) {
                        var extension = files[i].name.substr((files[i].name.lastIndexOf('.') +1));
                        
                        if (allowedExtension.indexOf(extension) < 0) {
                            flag = 1;
                        } else {
                            // Fix: To show file name in frontend
                            if(files.length > 1){
                                $('#uploaded-file-id').text(files.length+" Files")
                            }else{
                                $('#uploaded-file-id').text(files[i].name)
                            }
                        }
                    }
                    if (flag) {
                        this_this.val('');
                        alert($t(" File type not allowed,Allowed file: "+self.options.allowedextensions));
                    }
                }
            });
            var reply_form = true;
            $(document).on('keyup','.wk_cc_input_text',function () {
                var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                var email = $('.wk_cc_input_text').val();
                var email_arr = email.split(',');
                $.each(email_arr,function (i,e) {
                    reply_form = regex.test(e);
                })
                if (reply_form == false) {
                    $('#cc-err').show();
                } else {
                    $('#cc-err').hide();
                }
            })
        }
    });
    return $.mage.viewticket;
});
