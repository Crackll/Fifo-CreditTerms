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

class OrderShipmentViewModel implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * construct
     *
     * @param \Webkul\Marketplace\Helper\Orders $mpOrder
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Shipping\Helper\Data $shippingHelper
     * @param \Webkul\MpMSI\Helper\Data $msiHelper
     * @param \Magento\Framework\Json\Helper\Data $jsoHelper
     */
    public function __construct(
        \Webkul\Marketplace\Helper\Orders $mpOrder,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Shipping\Helper\Data $shippingHelper,
        \Webkul\MpMSI\Helper\Data $msiHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->mpOrder= $mpOrder;
        $this->mpHelper= $mpHelper;
        $this->shippingHelper= $shippingHelper;
        $this->msiHelper= $msiHelper;
        $this->jsonHelper= $jsonHelper;
    }

    /**
     * Get mp order helper
     */
    public function getMpOrders()
    {
        return $this->mpOrder;
    }

    /**
     * Get shipping helper
     */
    public function getShippingHelper()
    {
        return $this->shippingHelper;
    }

    /**
     * Get mp helper
     */
    public function getMpHelper()
    {
        return $this->mpHelper;
    }

    /**
     * Get mp msi helper
     */
    public function getMsiHelper()
    {
        return $this->msiHelper;
    }

    /**
     * Get json helper
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }
}
