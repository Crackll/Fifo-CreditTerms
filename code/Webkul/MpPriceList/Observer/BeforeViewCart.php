<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPriceList
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPriceList\Observer;

use Magento\Framework\Event\ObserverInterface;

class BeforeViewCart implements ObserverInterface
{
    /**
     * @var \Webkul\MpPriceList\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Webkul\MpPriceList\Helper\Data $helper
     */
    public function __construct(\Webkul\MpPriceList\Helper\Data $helper)
    {
        $this->_helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_helper->checkStatus();
    }
}
