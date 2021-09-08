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

use Webkul\MpWalletSystem\Model\WallettransactionFactory;
use Magento\Sales\Model\Order;

/**
 * Webkul MpWalletSystem Block
 */
class MpWalletSystem extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * Initialize dependencies
     *
     * @param \Magento\Backend\Block\Template\Context                   $context
     * @param \Magento\Sales\Model\OrderFactory                         $order
     * @param \Magento\Payment\Model\ResourceModel\Grid\TypeListFactory $typeList
     * @param array                                                     $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Model\OrderFactory $order,
        \Magento\Payment\Model\ResourceModel\Grid\TypeListFactory $typeList,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->order = $order;
        $this->typeList = $typeList;
    }

    /**
     * Retrieve row column field value for display
     *
     * @param  \Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowField(\Magento\Framework\DataObject $row)
    {
        if ($row->getOrderId() != "") {
            $orderId = $row->getOrderId();
            $salesOrder = $this->order->create();
            $order = $salesOrder->load($orderId);
            if ($order->getWalletAmount()) {
                if ($row->getMethod() != "mpwalletsystem") {
                    //get list of payment methods
                    $paymentMethodsList = $this->typeList->create()->toOptionArray();
                    return $paymentMetthod = $paymentMethodsList[$row->getMethod()]." + Webkul Wallet System";
                }
            }
        }
        return parent::getRowField($row);
    }
}
