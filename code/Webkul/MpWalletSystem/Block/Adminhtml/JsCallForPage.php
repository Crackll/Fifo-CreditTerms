<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWalletSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWalletSystem\Block\Adminhtml;

/**
 * Webkul MpWalletSystem Block
 */
class JsCallForPage extends \Magento\Backend\Block\Template
{
    /**
     * Initialize dependencies
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Model\Order              $order
     * @param \Magento\Framework\Json\Helper\Data     $helper
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\Json\Helper\Data $helper,
        array $data = []
    ) {
        $this->order = $order;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @var string
     */
    protected $_template = 'js.phtml';

    public function getUserHasWallet()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->order->load($orderId);
        return $order->getCustomerId();
    }

    /**
     * Get JSON helper
     *
     * @return object
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
