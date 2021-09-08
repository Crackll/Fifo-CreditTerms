<?php
/**
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPurchaseManagement\Controller\Adminhtml\Order;

class Receive extends \Webkul\MpPurchaseManagement\Controller\Adminhtml\Order
{
    /**
     * @var \Webkul\MpPurchaseManagement\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Webkul\MpPurchaseManagement\Model\OrderItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var \Webkul\MpPurchaseManagement\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Backend\App\Action\Context                 $context
     * @param \Webkul\MpPurchaseManagement\Model\OrderFactory     $orderFactory
     * @param \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory
     * @param \Webkul\MpPurchaseManagement\Helper\Data            $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\MpPurchaseManagement\Model\OrderFactory $orderFactory,
        \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory,
        \Webkul\MpPurchaseManagement\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->helper = $helper;
    }

    /**
     * purchase order update status
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $orderId = $this->getRequest()->getParam('id');
        if (!$orderId) {
            $this->messageManager->addError(__('Some error occured, Please try again'));
            return $resultRedirect->setPath('mppurchasemanagement/order/index');
        }
        $order = $this->orderFactory->create()->load($orderId);
        if ($order->getStatus()==\Webkul\MpPurchaseManagement\Model\Order::STATUS_SHIPPED) {
            $order->setStatus(\Webkul\MpPurchaseManagement\Model\Order::STATUS_COMPLETE);
            $order->setEntityId($orderId);
            $order->save();

            //receive order items
            $orderItemCollection = $this->orderItemFactory->create()->getCollection()
                                        ->addFieldToFilter('purchase_order_id', ['eq'=>$orderId]);
            foreach ($orderItemCollection as $item) {
                $this->helper->updateProductStock($item->getProductId(), $item->getQuantity());
                $status = \Webkul\MpPurchaseManagement\Model\OrderItem::STATUS_RECEIVED;
                $this->setStatus($item, $status);
            }
            $this->messageManager->addSuccess(__('Purchase Order items successfully received'));
        } else {
            $this->messageManager->addError(__('Purchase Order items can not be received'));
        }
        return $resultRedirect->setPath('mppurchasemanagement/order/view', ['id' => $order->getEntityId()]);
    }

    /**
     * Update Ship Status and receieved Qty
     *
     * @param collection $item
     * @param int $status
     * @return void
     */
    public function setStatus($item, $status)
    {
        $itemId = $item->getEntityId();
        $item->setShipStatus($status);
        $item->setReceivedQuantity($item->getQuantity());
        $item->setEntityId($itemId);
        $item->save();
    }
}
