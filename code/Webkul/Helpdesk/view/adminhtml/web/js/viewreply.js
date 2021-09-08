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
    'mage/adminhtml/wysiwyg/tiny_mce/setup',
    'mage/calendar'
], function ($, $t, mageTemplate, tiny_mce) {
    'use strict';
    $.widget('mage.viewreply', {
        _create: function () {
            var self = this;
            var wysiwygcompany_description = new wysiwygSetup("wk_reply_text_field", {
                "width":"99%",  // defined width of editor
                "height":"200px", // height of editor
                "plugins":[{"name":"image"}], // for image
                "tinymce4":{"toolbar":"formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap","plugins":"advlist autolink lists link charmap media noneditable table contextmenu paste code help table",
                }
            });
            wysiwygcompany_description.setup("exact");
            var wk_forward_description = new wysiwygSetup("wk_forward_text_field", {
                "width":"99%",  // defined width of editor
                "height":"200px", // height of editor
                "plugins":[{"name":"image"}], // for image
                "tinymce4":{"toolbar":"formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap","plugins":"advlist autolink lists link charmap media noneditable table contextmenu paste code help table",
                }
            });
            wk_forward_description.setup("exact");
            var wk_forward_description = new wysiwygSetup("wk_add_note_text_field", {
                "width":"99%",  // defined width of editor
                "height":"200px", // height of editor
                "plugins":[{"name":"image"}], // for image
                "tinymce4":{"toolbar":"formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap","plugins":"advlist autolink lists link charmap media noneditable table contextmenu paste code help table",
                }
            });
            wk_forward_description.setup("exact");
            var remianingThread = parseInt(self.options.remianingThread);
            var threadLimit = parseInt(self.options.threadLimit);
            var totalThread = parseInt(self.options.totalThread);


            setInterval(function () {
                self.saveInDraft();
            }, parseInt(self.options.draftsavetime));

            var allowedExtension = self.options.allowedextensions;

            $('.wk_ts_forms_container .bs-searchbox input').on('keypress', function (e) {
                e.stopPropagation();
            });

            $(".msg_submit_btn").on("click",function (e) {
                e.preventDefault();
                var textareaId;
                $(".wk_ts_forms_container_li").each(function () {
                    if ($(this).is(':visible') == true) {
                        textareaId = $(this).find('textarea').attr('id');
                    }
                 });
                 if (tinymce.get(textareaId).getContent() != "") {
                    $(this).parents("form").submit();
                } else {
                    $(".required-field-error-msg").show();
                    setTimeout(function () {
                        $(".required-field-error-msg").fadeOut();
                    }, 3000);
                }
            });

            $(".wk_ts_nav_tabs li").on("click",function () {
                $(this).siblings().removeClass("active");
                $(this).addClass("active");
                $($(this).attr("for")).siblings().hide();
                $($(this).attr("for")).show();
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
                        data : {currnetpage : this_this.attr('data-next-page'),id :self.options.ticketid},
                        dataType : 'json',
                        success : function (content) {
                            remianingThread = remianingThread - threadLimit;
                            count = count + 1;
                            this_this.attr('data-next-page',count);
                            var html = "";
                            $(content.listing).find(".wk_ts_one_thread").each(function () {
                                html = html + $(this).html();
                            });
                            $(".wk_ts_thread_container_box").prepend(html);
                            if (remianingThread > 0) {
                                $(".wk_ts_more_expand_link").html("<span class='thread_count'>"+remianingThread+"</span>"+$t('More Expand')+"<i class='fa fa-chevron-down'></i>");
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
                $('.'+divId.substring(1)+'-tab').trigger('click');
                $('html,body').animate({
                    scrollTop: $(divId).offset().top
                },'slow');
            });

            $('.wk_ts_ticket_action_box select,.wk_ts_left_sidebar .ticket_property select').on('change', function () {
                var this_this = $(this);
                if (this_this.val() != "") {
                    self.showSpinner();
                    $.ajax({
                        url : self.options.changepropertyUrl,
                        type : 'GET',
                        data : {field : this_this.attr('name'),id : self.options.ticketid,value:this_this.val()},
                        dataType : 'json',
                        success : function (content) {
                            self.hideSpinner();
                            $(".ajax-success-msg").hide();
                            $(".ajax-success-msg .msg").text($t("Success ! you have been successfully modified Tickets."));
                            $(".ajax-success-msg").show();
                            setTimeout(function () {
                                $(".ajax-success-msg").fadeOut();
                            }, 3000);
                        }
                    });
                }
            });

            $('.action_button.mark_spam').on('click', function () {
                var this_this = $(this);
                self.showSpinner();
                var status = self.options.spamstatus;
                $.ajax({
                    url : self.options.changepropertyUrl,
                    type : 'GET',
                    data : {field : 'status',id : self.options.ticketid,value:status},
                    dataType : 'json',
                    success : function (content) {
                        self.hideSpinner();
                        $(".ajax-success-msg").hide();
                        $('.ticket_status option[value="'+status+'"]').attr("selected", "selected");
                        $(".ajax-success-msg .msg").text($t('Success ! you have been successfully modified Tickets.'));
                        $(".ajax-success-msg").show();
                        setTimeout(function () {
                            $(".ajax-success-msg").fadeOut();
                        }, 3000);
                    }
                });
            });

            $('#add_personal_notes').on('keypress', function (e) {
                if (e.keyCode == 13) {
                    var this_this = $(this);
                    if (this_this.val() != "") {
                        self.showSpinner();
                        $.ajax({
                            url : self.options.addPersonalNoteUrl,
                            type : 'GET',
                            data : {id : self.options.ticketid,value:this_this.val()},
                            dataType : 'json',
                            success : function (content) {
                                self.hideSpinner();
                                var context = {"note_id":content,"note_description":this_this.val()};
                                var progressTmpl = mageTemplate($(".personal_note_row_tmp").html()), tmp;
                                tmp = progressTmpl({
                                    data:context
                                });
                                this_this.parents(".form-group").find(".ticket_property").after(template(tmp));
                                this_this.val("");
                                $(".ajax-success-msg").hide();
                                $(".ajax-success-msg .msg").text($t("Success ! you have been successfully modified Tickets."));
                                $(".ajax-success-msg").show();
                                setTimeout(function () {
                                    $(".ajax-success-msg").fadeOut();
                                }, 3000);
                            }
                        });
                    }
                }
            });

            $('.action_button.delete').on("click",function () {
                var id = self.options.ticketid;
                var dicisionapp = confirm($t("Are you sure you want to delete this ticket ?"));
                if (dicisionapp == true) {
                    window.location = self.options.ticketdeleteUrl.concat("id/",id);
                }
            });

            $('body').delegate('.wk_ts_pannel_heading.customer',"click",function () {
                var progressTmpl = mageTemplate($(".customer_detail_popup_template").html());
                $(this).append(progressTmpl);
            });

            $(".apply_action").on("click",function () {
                var progressTmpl = mageTemplate($(".popup_container_template").html());
                $("body").append(progressTmpl);
                $(".page-wrapper").css("opacity","0.35");
            });

            $("body").delegate(".close_pop_up,.popup_close_btn","click",function () {
                $(".page-wrapper").css("opacity","1");
                $(".popup_container").remove();
            });

            $(".wk_ts_thread_container").delegate(".wk_ts_one_thread_body","mouseover",function () {
                $(this).find(".deletethread").show();
                $(this).find(".splitthread").show();
            });

            $(".wk_ts_thread_container").delegate(".wk_ts_one_thread_body","mouseout",function () {
                $(this).find(".deletethread").hide();
                $(this).find(".splitthread").hide();
            });

            $(".wk_ts_thread_container").delegate(".deletethread","click",function () {
                var this_this = $(this);
                var id = $(this).attr("data-id");
                var dicisionapp = confirm($t('Are you sure you want to delete this thread ? '));
                if (dicisionapp == true) {
                    self.showSpinner();
                    $.ajax({
                        url : self.options.deletethreadUrl,
                        type : 'GET',
                        data : {id :id},
                        dataType : 'json',
                        success : function (content) {
                            self.hideSpinner();
                            $(".ajax-success-msg").hide();
                            this_this.parents(".wk_ts_one_thread").remove();
                            $(".ajax-success-msg .msg").text($t("Success ! you have been successfully deleted thread."));
                            $(".ajax-success-msg").show();
                            setTimeout(function () {
                                $(".ajax-success-msg").fadeOut();
                            }, 3000);
                        }
                    });
                }
            });

            $(".wk_ts_thread_container").delegate(".splitthread","click",function () {
                var this_this = $(this);
                var id = $(this).attr("data-id");
                var dicisionapp = confirm($t(" Are you sure you want to split this ticket ? "));
                if (dicisionapp == true) {
                    self.showSpinner();
                    $.ajax({
                        url : self.options.splitthreadUrl,
                        type : 'GET',
                        data : {id :id},
                        dataType : 'json',
                        success : function (content) {
                            self.hideSpinner();
                            $(".ajax-success-msg").hide();
                            this_this.parents(".wk_ts_one_thread").remove();
                            $(".ajax-success-msg .msg").text($t('Success ! you have been successfully split thread as ticket #')+content);
                            $(".ajax-success-msg").show();
                            setTimeout(function () {
                                $(".ajax-success-msg").fadeOut();
                            }, 3000);
                        }
                    });
                }
            });

            $(document).on("click",function (event) {
                event.stopPropagation();
                var className = event.target.className;
                var linkClassName = event.target.className.split(" ")[0];
                if (className != 'arrow_box' && linkClassName != 'wk_ts_pannel_heading') {
                    $(".arrow_box").remove();
                }
            });

            $(".wk_ts_attachment_block").on("mouseover",function () {
                $(this).find("a").show();
            });

            $(".wk_ts_attachment_block").on("mouseout",function () {
                $(this).find("a").hide();
            });

            $(".wk_ts_left_sidebar").delegate(".personal_note_row","mouseover",function () {
                $(this).find(".note_delete_box").show();
            });

            $(".wk_ts_left_sidebar").delegate(".personal_note_row","mouseout",function () {
                $(this).find(".note_delete_box").hide();
            });

            $(".wk_ts_left_sidebar").delegate(".personal_note_check","click",function () {
                var status = 0;
                var this_this = $(this);
                if ($(this).is(':checked')) {
                    $(this).next().css("text-decoration","line-through");
                    status = 2;
                } else {
                    $(this).next().css("text-decoration","none");
                    status = 1;
                }
                $.ajax({
                    url : self.options.changeNoteStatusUrl,
                    type : 'GET',
                    data : {id : this_this.val(),status:status},
                    dataType : 'json',
                    success : function (content) {
                        $(".ajax-success-msg").hide();
                        $(".ajax-success-msg .msg").text($t("Success ! you have been successfully modified Tickets."));
                        $(".ajax-success-msg").show();
                        setTimeout(function () {
                            $(".ajax-success-msg").fadeOut();
                        }, 3000);
                    }
                });
            });

            $(".wk_ts_left_sidebar").delegate(".note_delete_box","click",function () {
                var dicisionapp=confirm($t(" Are you sure you want to delete ? "));
                if (dicisionapp === false) {
                    return false;
                }

                var this_this = $(this);
                $.ajax({
                    url : self.options.deleteNoteUrl,
                    type : 'GET',
                    data : {id :this_this.siblings(".personal_note_check").val()},
                    dataType : 'json',
                    success : function (content) {
                        this_this.parents(".personal_note_row").remove();
                        $(".ajax-success-msg").hide();
                        $(".ajax-success-msg .msg").text($t("Success ! you have been successfully deleted note."));
                        $(".ajax-success-msg").show();
                        setTimeout(function () {
                            $(".ajax-success-msg").fadeOut();
                        }, 3000);
                    }
                });
            });

            $(".bs-searchbox").on("click", function (e) {
                e.stopPropagation();
            });

            $(".dropdown-toggle").on("click",function (e) {
                e.stopPropagation();
                $(".dropdown-menu").hide();
                $(this).next().toggle();
            });

            $(".ticket_tag_block").delegate(".dropdown-menu-list li","click",function (e) {
                e.stopPropagation();
                var this_this = $(this);
                self.showSpinner();
                if (this_this.attr("data-active") == 1) {
                    this_this.find("i").removeClass("show");
                    this_this.find("i").addClass("hide");
                    this_this.attr("data-active","0");
                } else {
                    this_this.find("i").removeClass("hide");
                    this_this.find("i").addClass("show");
                    this_this.attr("data-active","1");
                }
                $.ajax({
                    url : self.options.addtagUrl,
                    type : 'GET',
                    data : {id :this_this.attr("data-id"),flag:this_this.attr("data-active"),ticket_id:self.options.ticketid},
                    dataType : 'html',
                    success : function (content) {
                        self.hideSpinner();
                        if (content == "") {
                            this_this.parents(".ticket_tag_block").find(".filter-option").text($t('Nothing selected'));
                        } else {
this_this.parents(".ticket_tag_block").find(".filter-option").text(content);
                        }
                        $(".ajax-success-msg").hide();
                        $(".ajax-success-msg .msg").text($t("Success ! you have been successfully modified note."));0
                        $(".ajax-success-msg").show();
                        setTimeout(function () {
                            $(".ajax-success-msg").fadeOut();
                        }, 3000);
                    }
                });
            });

            $(".ticket_tag_block .bs-searchbox input").on("input",function () {
                var this_this = $(this);
                var flag = 0;
                this_this.parents(".dropdown-menu").find(".dropdown-menu-list li").each(function () {
                    var text = $(this).find(".text").text();
                    var n = text.search(this_this.val());
                    if (n < 0) {
                        $(this).hide();
                    } else {
                        $(this).show();
                        flag = 1;
                    }
                });
                if (flag == 0) {
                    $(this).parents(".form-col").find(".no-results").show();
                } else {
                    $(this).parents(".form-col").find(".no-results").hide();
                }
            });

            $('.ticket_tag_block .bs-searchbox input').on('keypress', function (e) {
                if (e.keyCode == 13) {
                    var this_this = $(this);
                    if (this_this.val() != "") {
                        self.showSpinner();
                        $.ajax({
                            url : self.options.savetagUrl,
                            type : 'GET',
                            data : {value:this_this.val(),ticket_id:self.options.ticketid},
                            dataType : 'json',
                            success : function (content) {
                                var progressTmpl = mageTemplate($(".tag_row_tmp").html());
                                var tmp = progressTmpl({
                                    data:content
                                });
                                $(".ticket_tag_block .dropdown-menu-list").append(tmp);
                                self.hideSpinner();
                                this_this.val("");
                                $(".ajax-success-msg").hide();
                                $(".ticket_tag_block .ticket_tag_btn .filter-option").text(content.names);
                                $(".ajax-success-msg .msg").text($t('Success ! you have been successfully modified note.'));
                                $(".ajax-success-msg").show();
                                $(".ticket_tag_block .dropdown-menu-list li").show();
                                $(".ticket_tag_block .dropdown-menu-list").find(".no-results").hide();
                                setTimeout(function () {
                                    $(".ajax-success-msg").fadeOut();
                                }, 3000);
                            }
                        });
                    }
                }
            });

            $(".wk_ts_forms_container .bs-searchbox input").on("input",function () {
                var this_this = $(this);
                var flag = 0;
                this_this.parents(".dropdown-menu").find(".dropdown-menu-list li").each(function () {
                    var text = $(this).find(".text").text();
                    var n = text.search(this_this.val());
                    if (n < 0) {
                        $(this).hide();
                    } else {
                        $(this).show();
                        flag = 1;
                    }
                });
                if (flag == 0) {
                    $(this).parents(".dropdown-menu").find(".no-results").show();
                } else {
                    $(this).parents(".dropdown-menu").find(".no-results").hide();
                }
            });

            $('.wk_ts_forms_container .bs-searchbox input').on('keypress', function (e) {
                if (e.keyCode == 13) {
                    var names = "";
                    var email = "";
                    var context = "";
                    var email_arr = [];
                    $(".required-field-error-msg").hide();
                    var this_this = $(this);
                    if (this_this.val() != "") {
                        if (self.validateEmail(this_this.val())) {
                            alert('Email is valid');
                        } else {
                            alert('Invalid Email Address');
                            return false;
                        }
                        context = {"id":"0","name":this_this.val()};
                        var progressTmpl = mageTemplate($(".tag_row_tmp").html()), tmpl;
                        tmpl = progressTmpl({
                            data: context
                        });
                        $(this).parents(".dropdown-menu").find(".dropdown-menu-list").append(tmpl);
                        this_this.parents(".dropdown-menu").find("li").show();
                        $(this).parents(".dropdown-menu").find(".dropdown-menu-list").find(".no-results").hide();
                        this_this.parents(".dropdown-menu").find("li").each(function () {
                            if ($(this).attr("data-active") == 1) {
                                names = names + $(this).find(".text").text()+",";
                                email = $(this).find(".text").attr("data-email");
                                email_arr.push(email);
                            }
                        });
                        this_this.val("");
                        this_this.parents(".dropdown-menu").parents("ul").find("."+this_this.parents(".dropdown-menu").prev().find(".filter-option").attr("for")).val(email_arr.join());
                        this_this.parents(".dropdown-menu").parent().find(".filter-option").text(names);
                    }
                }
            });

            $(".wk_ts_forms_container").delegate(".dropdown-menu-list li","click",function (e) {
                e.stopPropagation();
                var this_this = $(this);
                if (this_this.attr("data-active") == 1) {
                    this_this.find("i").removeClass("show");
                    this_this.find("i").addClass("hide");
                    this_this.attr("data-active","0");
                } else {
                    this_this.find("i").removeClass("hide");
                    this_this.find("i").addClass("show");
                    this_this.attr("data-active","1");
                }
                var names = "";
                var email = "";
                this_this.parents(".dropdown-menu").find("li").each(function () {
                    if ($(this).attr("data-active") == 1) {
                       names = names + $(this).find(".text").text()+",";
                       email = email + $(this).find(".text").attr("data-email")+",";
                    }
                });
                if (names == "") {
                    this_this.parents(".dropdown-menu").parent().find(".filter-option").text(this_this.parents(".dropdown-menu-list").parents("ul").find(".filter-option").attr("for"));
                    this_this.parents(".dropdown-menu-list").parents("ul").find("."+this_this.parents(".dropdown-menu").prev().find(".filter-option").attr("for")).val(email);
                } else {
                    this_this.parents(".dropdown-menu").parent().find(".filter-option").text(names);
                    this_this.parents(".dropdown-menu-list").parents("ul").find("."+this_this.parents(".dropdown-menu").prev().find(".filter-option").attr("for")).val(email);
                }
            });

            $(document).on("click", function () {
                $(".dropdown-menu").hide();
            });

            $('#ticket-viewers').on('click', function (e) {
                self.checkViewer();
            });

            $(document).on("ready", function () {
                self.checkViewer();
            });

            setInterval(function () {
                self.checkViewer();
            }, parseInt(self.options.lockviewtime));

            $('#wk_ts_file,#wk_ts_file1').on('change',function (e) {
                var this_this = $(this);
                if ($(this).get(0).files.length > parseInt(self.options.numAllowFile)) {
                    e.stopImmediatePropagation();
                    $(this).val('');
                    alert('Can not upload more than '+self.options.numAllowFile+' images');
                } else {
                    var flag = 0;
                    var files = e.originalEvent.target.files;
                    for (var i = 0, len = files.length; i < len; i++) {
                        var extension = files[i].name.substr((files[i].name.lastIndexOf('.') +1));
                        if (allowedExtension.indexOf(extension) < 0) {
                            flag = 1;
                        }else {
                            // Fix: To show file name in frontend
                            if(files.length > 1){
                                $('#uploaded-file-id').text(files.length+" Files Uploaded!!")
                            }else{
                                $('#uploaded-file-id').text(files[i].name)
                            }
                        }
                    }
                    if (flag) {
                        this_this.val('');
                        alert(" File type not allowed,Allowed file: " + self.options.allowedExtension);
                    }
                }
            });
        },

        saveInDraft : function () {
            var self = this;
            var fieldType = "";
            var content = "";
            if ($(".wk_ts_nav_tabs li.active").attr("for") == "#wk_ts_reply_container") {
                fieldType = "reply";
                if (parseInt(self.options.alloweditor)) {
                    content = tinymce.get('wk_reply_text_field').getContent();
                } else {
                    content = $('#wk_reply_text_field').val();
                }
            } else if ($(".wk_ts_nav_tabs li.active").attr("for") == "#wk_ts_forward_container") {
                fieldType = "forward";
                if (parseInt(self.options.alloweditor)) {
                    content = tinymce.get('wk_forward_text_field').getContent();
                } else {
                    content = $('#wk_forward_text_field').val();
                }
            } else if ($(".wk_ts_nav_tabs li.active").attr("for") == "#wk_ts_add_note_container") {
                fieldType = "note";
                if (parseInt(self.options.alloweditor)) {
                    content = tinymce.get('wk_add_note_text_field').getContent();
                } else {
                    content = $('#wk_add_note_text_field').val();
                }
            }

            if (content != "") {
                this.showSpinner();
                $.ajax({
                    url : self.options.saveindraftUrl,
                    type : 'POST',
                    data : {form_key: self.options.formkey,field:fieldType,id : self.options.ticketid,user_id:$("#agent_id").val(),content:content},
                    dataType : 'json',
                    success : function (content) {
                        $(".ajax-draft-save-msg").show();
                        setTimeout(function () {
                            $(".ajax-draft-save-msg").fadeOut();
                        }, 3000);
                        self.hideSpinner();
                        $("#draft_msg_box").text($t('Draft Saved'));
                    }
                });
            }
        },
        checkViewer : function () {
            var self = this;
            $('#ticket-viewers i').removeClass("fa-eye").addClass("fa-spin fa-spinner");
            $.ajax({
                url : self.options.checkViewerUrl,
                type : 'GET',
                data : {ticket_id:self.options.ticketid},
                dataType : 'json',
                success : function (content) {
                    $('#ticket-viewers i').removeClass("fa-spin fa-spinner").addClass("fa-eye");
                    if (parseInt(content.no) > 0) {
                        $('#ticket-viewers').removeClass("wk_primary");
                        $('#ticket-viewers').addClass("wk_faliure");
                    } else {
                        $('#ticket-viewers').removeClass("wk_faliure");
                        $('#ticket-viewers').addClass("wk_primary");
                    }
                    $("#ticket-viewers .down_arrow_box .count").text(content.no);
                    $("#ticket-viewers .down_arrow_box .names").text(content.viewers);
                }
            });
        },

        validateEmail : function (email) {
            var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
            if (filter.test(email)) {
                return true;
            } else {
                return false;
            }
        },

        showSpinner : function () {
            $("#wk_loader").show();
            $("#wk_loader").prev().hide();
        },

        hideSpinner : function () {
            $("#wk_loader").hide();
            $("#wk_loader").prev().show();
        },
    });
    return $.mage.viewreply;
});
