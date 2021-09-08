<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorAttributeManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpVendorAttributeManager\Block;

use \Magento\Framework\View\Element\Template\Context;

class Sellerregistration extends \Webkul\Marketplace\Block\Sellerregistration
{
    /**
     * @param Context $context
     * @param \Webkul\MpVendorAttributeManager\Helper\Data $mpHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Webkul\MpVendorAttributeManager\Helper\Data $mpHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->mpHelper = $mpHelper;
        parent::__construct($context, $data);
    }

    public function getMpHelper()
    {
        return $this->mpHelper;
    }

    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }
}
