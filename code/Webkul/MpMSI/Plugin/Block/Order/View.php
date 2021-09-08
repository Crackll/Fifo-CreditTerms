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

namespace Webkul\MpMSI\Plugin\Block\Order;

class View
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
     * @param \Webkul\Marketplace\Block\Order\View $subject
     * @param string $template
     *
     * @return string
     */
    public function beforeSetTemplate(\Webkul\Marketplace\Block\Order\View $subject, $template)
    {

        if (!$this->helper->isSingleStoreMode() && $subject->getShipment() &&
            $template == "Webkul_Marketplace::order/shipment/view.phtml" ) {
            return "Webkul_MpMSI::orders/view.phtml";
        }
        
        return $template;
    }
}
