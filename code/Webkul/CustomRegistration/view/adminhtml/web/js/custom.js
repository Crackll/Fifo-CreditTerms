/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomRegistration
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define('js/theme', [
    'jquery',
    'domReady!'
], function ($) {
    'use strict';
    $('body').on('change', '#dependable_attribute_code', function () {
      var customAttrInput = $('body').find('#customfields_attribute_code');
        if (customAttrInput.val() == $(this).val()) {
           $(this).val('');
           alert('Dependable Field Attribute Code can Not be same.')
        }
    });

    $(document).ready(function () {
        
        $(document).on('click', "#tab_custom_registration", function () {
            setTimeout(function () {
                $("div[class*='dependable_field_'] select").each(function() {
                    var parent = $(this).parents("div[class*='dependable_field_']");
                    var childFieldClass = parent.data('index');
                    var child = $('div.child_'+childFieldClass);
                    $(parent).after(child);

                    var attrValue = $.trim($(this).find("option:selected").text());
                    updateFields(childFieldClass, attrValue);
                });

                $(document).on('change', "div[class*='dependable_field_'] select", function () {
                    var attrValue = $.trim($(this).find("option:selected").text());
                    var childClass = $(this).parents("div[class*='dependable_field_']").data('index');
                    updateFields(childClass, attrValue);
                });
            }, 1000);
        });

        
    });

    function updateFields(childClass, attrValue)
    {
        if (childClass != '') {
            if (attrValue == 'Yes') {
                $(document).find('div.child_'+childClass).show();
            } else if (attrValue == 'No') {
                $(document).find('div.child_'+childClass).hide();
            }
        }
    }
});
