<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPurchaseManagement\Block\MpWholesale;
 
class Navigation extends \Webkul\MpWholesale\Block\Account\Navigation
{
    public function getOrderListUrl()
    {
        return $this->getUrl('mppurchasemanagement/order/list', [
            '_secure' => $this->getRequest()->isSecure()
        ]);
    }
}
