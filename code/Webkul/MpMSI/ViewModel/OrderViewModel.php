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

namespace Webkul\MpMSI\ViewModel;

class OrderViewModel implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * construct
     *
     * @param \Webkul\Marketplace\Helper\Orders $mpOrder
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     */
    public function __construct(
        \Webkul\Marketplace\Helper\Orders $mpOrder,
        \Webkul\Marketplace\Helper\Data $mpHelper
    ) {
        $this->mpOrder= $mpOrder;
        $this->mpHelper= $mpHelper;
    }
    public function getMpOrders()
    {
        return $this->mpOrder;
    }
    
    public function getMpHelper()
    {
        return $this->mpHelper;
    }
}
