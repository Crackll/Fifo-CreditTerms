<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMSI
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpMSI\Plugin\Block\Product;

class Listing
{

    /**
     * @var  \Webkul\MpMSI\Helper\Data
     */
    protected $helper;

    public function __construct(
        \Webkul\MpMSI\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * before set template
     * @param \Webkul\Marketplace\Block\Product\Productlist $subject
     * @param string $template
     *
     * @return string
     */
    public function beforeSetTemplate(\Webkul\Marketplace\Block\Product\Productlist $subject, $template)
    {
        if (!$this->helper->isSingleStoreMode() && $template == "product/myproductlist.phtml") {
            return "Webkul_MpMSI::product/list.phtml";
        }else if($template == "account/deallist.phtml"){
            return "Webkul_MpDailyDeal::account/deallist.phtml";
        }
        
        return "Webkul_Marketplace::product/myproductlist.phtml";
    }
}
