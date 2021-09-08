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

namespace Webkul\MpWalletSystem\Plugin\Block\Widget\Button;

use Magento\Backend\Block\Widget\Button\Toolbar as ToolbarContext;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Backend\Block\Widget\Button\ButtonList;

class Toolbar
{
    /**
     * Initialize dependencies
     *
     * @param \Webkul\MpWalletSystem\Helper\Data $helper
     * @param \Magento\Sales\Model\OrderFactory  $order
     */
    public function __construct(
        \Webkul\MpWalletSystem\Helper\Data $helper,
        \Magento\Sales\Model\OrderFactory $order
    ) {
        $this->helper = $helper;
        $this->order = $order;
    }

    /**
     * Before plugin of pushButtons fucntion
     *
     * @param ToolbarContext $toolbar
     * @param \Magento\Framework\View\Element\AbstractBlock $context
     * @param \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
     * @return array
     */
    public function beforePushButtons(
        ToolbarContext $toolbar,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    ) {
        $orderId = $this->helper->getOrderIdFromUrl();
        $order = $this->order->create()->load($orderId);
        $orderItems = $order->getAllVisibleItems();
        $flag = false;
        foreach ($orderItems as $item) {
            if ($item->getSku() == "wk_wallet_amount") {
                $flag = true;
            }
        }
        if ($flag) {
            $buttonList->remove('order_invoice');
            $buttonList->remove('order_creditmemo');
        }
        return [$context, $buttonList];
    }
}
