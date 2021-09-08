/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RegionUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    [
    "jquery",
    "Magento_Ui/js/modal/modal",
    "mage/template",
    'mage/translate',
    ], function ($,modal,template) {
        'use strict';
        $.widget(
            'mage.WkRegionUpload', {
                options: {
                    
                },
                _create: function () {
                    var self = this;
                    var row_id = self.options.counter;
                    var options = {
                        type: 'slide',
                        responsive: true,
                        innerScroll: true,
                        modalClass: 'popup', 
                        validation:{},
                        buttons: [
                            {
                                text: $.mage.__('Submit'),
                                class: 'action-primary',
                                click: function () {
                                    var form = $('#wk_regionupload_popup');
                                    $('#wk_regionupload_popup').submit();
                                }
                            }
                        ]
                    };
                    var popup = modal(options, $('#wk_regionupload_popup'));
                    $("#wk_upload_csv").click(function() {
                        $("wk_regionupload_popup").css('display','block')
                        $("#wk_regionupload_popup").modal("openModal");
                        
                    });
                    $("#wk_submit").click(function() {
                        if($("#wk_country_id").val() !="" && $("#wk_region_code").val() !="" && $("#wk_region_name").val() !=""){
                            $('#wk_submit').attr('disabled','disabled');
                        } 
                        $('#wk_submit').attr('disabled','disabled');
                        $('#wk_region_add_form').submit();
                    });
                    $('#add-btn').on('click', function(){
                        // $('.body-regionname').append($('#row-template'));
                        var rowtemplate = template('#row-template');
                        var row = rowtemplate({
                                                data: {
                                                    id: row_id++
                                                }
                                            });
                            
                            $('.body-regionname').append(row);
                    });
                    $(document).on('click', '.action-delete', function() {
                        event.preventDefault();
                        $(this).parents('tr').remove();
                    });
                }
            }  
        )
        return $.mage.WkRegionUpload;
    }
);
