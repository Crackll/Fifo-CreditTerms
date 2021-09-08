/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AbandonedCart
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

require([
    "jquery",
    'Magento_Ui/js/modal/modal',
    'accordion',
    "mage/translate",
    "mage/adminhtml/events",
    "mage/adminhtml/wysiwyg/tiny_mce/setup"
], function($,modal){
    $("#element").accordion();
    $("#element").accordion({ active: "0 0"});


    var dataForm = $("#wk-abandon-cart-mail-form");
    dataForm.mage('validation', {});

    $("#submit_mail").click( function () {
        var flag = dataForm.valid();
        var mailBody = document.querySelector("#mceu_13");
        var length = document.querySelector("#mailBody").value.trim().length;
        if (!length) {
            mailBody.style.border = "1px solid red";
            document.querySelector(".wk-required-warning").style.display = "inline-block";
        } else {
            dataForm.submit();
            if (flag !== false) {
                $('body').trigger('processStart');
            }
        }
    });

    wysiwygDescription = new wysiwygSetup("mailBody", {
        "width":"100%",
        "height":"200px",
        "plugins":[{"name":"image"}],
        "tinymce4":{
            "toolbar":"formatselect | bold italic underline | alignleft aligncenter alignright | "
            +"bullist numlist | link table charmap",
            "plugins":"advlist autolink lists link charmap media noneditable table"+
            " contextmenu paste code help table",
        }
    });
    wysiwygDescription.setup("exact");
    var options = {
        type: 'popup',
        responsive: true,
        innerScroll: true,
        title: $.mage.__('Send Custom Mail'),
        required: true
    };
    var popup = modal(options, $('#popup_background'));
    $('#abandonedCartMail').click(function () {
        $('#popup_background').modal('openModal');
        $("#popup_background").show();
    });

    $('#reset_popup').click(function (e) {
        e.preventDefault();
        $('#adminName').val('');
        $('#adminEmail').val('');
        $('#mailBody').val('');
    });
});