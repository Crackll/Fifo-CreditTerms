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
            'webkul.viewPageLabelJs',
            {
                _create: function () {
                    var element = this.element;
                    var self = this;
                    $(".product.media").append(self.options.imageTag.imagePath);
                },
            }
        );
        return $.webkul.viewPageLabelJs;
    }
);