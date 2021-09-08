<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\ViewModel;

class Common implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    public $helper;
    public $mpHelper;
    public $jsonHelper;

    public function __construct(
        \Webkul\MpPushNotification\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->helper = $helper;
        $this->mpHelper = $mpHelper;
        $this->jsonHelper = $jsonHelper;
    }

    public function getHelper()
    {
        return $this->helper;
    }

    public function getMarketplaceHelper()
    {
        return $this->mpHelper;
    }

    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }
}
