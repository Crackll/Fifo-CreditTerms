<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\ViewModel;

class MpWholeSaleViewModel implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @param \Webkul\MpWholesale\Helper\Data $wholeSaleHelper
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Webkul\MpWholesale\Helper\Data $wholeSaleHelper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->wholeSaleHelper= $wholeSaleHelper;
        $this->mpHelper= $mpHelper;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Get WholeSaler Helper Object
     *
     * @return object
     */
    public function getWholeSaleHelper()
    {
        return $this->wholeSaleHelper;
    }

    /**
     * get MarketPlace Helper Objecr
     *
     * @return object
     */
    public function getMpHelper()
    {
        return $this->mpHelper;
    }

    /**
     * This function will return json encoded data
     *
     * @param json $data
     * @return Array
     */
    public function jsonEncodeData($data)
    {
        return $this->jsonHelper->jsonEncode($data, true);
    }
}
