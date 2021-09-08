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
            'webkul.sellerProfileJs',
            {
                _create: function () {
                    var element = this.element;
                    var self = this;
                    var baseurl = self.options.imageTag.base;
                    var productCollection = self.options.imageTag.productCollection;
                    var i = 0;
                    $(".product-item-info").each(function (index,element) {
                        var self1 = this;
                        $.ajax({
                            url: self.options.imageTag.url,
                            type: 'POST',
                            data: { id : productCollection[i]},
                            dataType: 'json',
                            success: function (data) {
                                var imgWidth = data['label_width_categorypage'];
                                var imgHeight = data['label_height_categoryage'];
                                if (data['position']=='1') {
                                    var img= "<img class=labels width="+imgWidth+"%  style='left:0%;top:4%;height:"+imgHeight+"%' alt="+data['name']+" title="+data['name']+" src="+baseurl+data['image_name']+">";
                                } else if (data['position']=='2') {
                                    var img= "<img class=labels width="+imgWidth+"%  style='right:0%;top:4%;height:"+imgHeight+"%'  alt="+data['name']+" title="+data['name']+" src="+baseurl+data['image_name']+">";
                                } else if (data['position']=='3') {
                                    var img= "<img class=labels width="+imgWidth+"%  style='left:0%;bottom:4%;height:"+imgHeight+"%' alt="+data['name']+" title="+data['name']+" src="+baseurl+data['image_name']+">";
                                } else if (data['position']=='4') {
                                    var img= "<img class=labels width="+imgWidth+"%  style='right:0%;bottom:4%;height:"+imgHeight+"%' alt="+data['name']+" title="+data['name']+" src="+baseurl+data['image_name']+">";
                                }
                                
                                $($(self1).parent().find("a").find("span").find(".product-image-wrapper")).prepend(img);
                            }
                        })
                        i++;
                    });
                },
            }
        );
        return $.webkul.sellerProfileJs;
    }
);