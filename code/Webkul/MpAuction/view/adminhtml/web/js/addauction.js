require([
    "jquery",
    "mage/mage"
], function ($) {
        var option = $('select[name=product_id] option:selected').val();
        //console.log(option)
        var urldisable=$("#wkauction_url").val();
    $("#wkauction_product_id").change(function () {
    $.ajax({
        url: urldisable,
        data: {form_key: window.FORM_KEY,option:$('#wkauction_product_id').val()},
        type: 'POST',
        dataType:'html',
        success: function (transport) {
            var response = $.parseJSON(transport);
            //console.log(response);
            if (response.disabled==true) {
                document.getElementById("wkauction_max_qty").value =1;
                document.getElementById("wkauction_min_qty").value =1;
                $(".admin__field.field.field-max_qty").hide()
                $(".admin__field.field.field-min_qty").hide()
            } else {
                $(".admin__field.field.field-max_qty").show()
                $(".admin__field.field.field-min_qty").show()
            }
        }
    });
});
    //console.log("uj");
    $('#save').click(function () {
        setTimeout(function () {
            //console.log("uh");
            if (!$(".admin__field-control [id$='-error']").length || $(".admin__field-control [id$='-error'][style='display: none;']").length == $(".admin__field-control [id$='-error']").length) {
                $('#save').attr('disabled', 'disabled');
                //console.log("addjs")
            }
        }, 1);
    })
});