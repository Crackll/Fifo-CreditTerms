<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomerSubaccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\CustomerSubaccount\Block\MultiShipping;

class Checkout extends \Magento\Framework\View\Element\Template
{
    /**
     * Context
     *
     * @var \Magento\Framework\View\Element\Template\Context
     */
    public $context;

    /**
     * Helper
     *
     * @var \Webkul\CustomerSubaccount\Helper\Data
     */
    public $helper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    public function isForcedAddress()
    {
        if ($this->helper->isForcedMainAddress()) {
            return true;
        }
        return false;
    }
}
