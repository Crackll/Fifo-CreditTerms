/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceProductLabels
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    [
      'jquery'
    ],
    function ($,$t) {
 
        $.widget(
            'webkul.labelPreview',
            {
                _create: function () {
                    $(":file").change(function () {
                        if (this.files && this.files[0]) {
                            var reader = new FileReader();
                            reader.onload = imageIsLoaded;
                            reader.readAsDataURL(this.files[0]);
                        }
                    });
                    function imageIsLoaded(e) {
                        $(".wk-label-show").css("display","block");
                        $('#customfields_your_image_image').attr('src', e.target.result);
                    };
                },
            }
        );
        return $.webkul.labelPreview;
    }
);