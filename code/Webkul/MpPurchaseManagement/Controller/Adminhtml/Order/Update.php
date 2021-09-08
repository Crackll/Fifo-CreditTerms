<?php
/**
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPurchaseManagement\Controller\Adminhtml\Order;

class Update extends \Webkul\MpPurchaseManagement\Controller\Adminhtml\Order
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
     * @var \Webkul\MpWholesale\Helper\Data
     */
    protected $wholesaleHelper;

    /**
     * @var \Webkul\MpPurchaseManagement\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Backend\App\Action\Context                 $context
     * @param \Webkul\MpPurchaseManagement\Model\OrderFactory     $orderFactory
     * @param \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory
     * @param \Webkul\MpWholesale\Helper\Data                     $wholesaleHelper
     * @param \Webkul\MpPurchaseManagement\Helper\Data            $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\MpPurchaseManagement\Model\OrderFactory $orderFactory,
        \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory,
        \Webkul\MpWholesale\Helper\Data $wholesaleHelper,
        \Webkul\MpPurchaseManagement\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->wholesaleHelper = $wholesaleHelper;
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
        $data = $this->getRequest()->getParams();
        if (!isset($data['id'])) {
            $this->messageManager->addError(__('Some error occured, Please try again'));
            return $resultRedirect->setPath('mppurchasemanagement/order/index');
        }
        $order = $this->orderFactory->create()->load($data['id']);
        if ($order->getStatus()==\Webkul\MpPurchaseManagement\Model\Order::STATUS_NEW) {
            $order->setStatus($data['status']);
            $order->setEntityId($data['id']);
            $order->save();
            if ($data['status']==\Webkul\MpPurchaseManagement\Model\Order::STATUS_CANCELLED) {
                $customerIds = [];
                $orderItemCollection = $this->orderItemFactory->create()->getCollection()
                                            ->addFieldToFilter('purchase_order_id', ['eq'=>$data['id']]);
                foreach ($orderItemCollection as $item) {
                    $itemId = $item->getEntityId();
                    if (!in_array($item->getSellerId(), $customerIds)) {
                        array_push($customerIds, $item->getSellerId());
                    }
                    $status = \Webkul\MpPurchaseManagement\Model\OrderItem::STATUS_CANCELLED;
                    $this->setStatus($item, $status);
                }
                $this->sendCancelMail($customerIds, $order);
            } else {
                $this->sendApprovalMail($order);
            }
            $this->messageManager->addSuccess(__('Purchase Order status successfully updated'));
        } else {
            $this->messageManager->addError(__('Purchase Order status cannot be updated'));
        }
        return $resultRedirect->setPath('mppurchasemanagement/order/view', ['id' => $order->getEntityId()]);
    }

    /**
     * send order cancel mail to seller
     * @param array
     * @param  \Webkul\MpPurchaseManagement\Model\Order
     * @return void
     */
    protected function sendCancelMail($customerIds, $order)
    {
        foreach ($customerIds as $id) {
            $customer = $this->wholesaleHelper->getCustomerData($id);
            $data = [
                'var_name' => $customer->getName(),
                'var_message' => __('Purchase Order %1 has been cancelled by Admin.', $order->getIncrementId()),
                'sender_email' => $this->wholesaleHelper->getAdminEmail(),
                'sender_name' => $this->wholesaleHelper->getAdminName(),
                'receiver_name' => $customer->getName(),
                'receiver_mail' => $customer->getEmail(),
                'subject' => __('Purchase Order Cancelled')
            ];
            $this->helper->sendUpdateStatusMail($data);
        }
    }

    /**
     * send order approval mail to wholesaler
     * @param  \Webkul\MpPurchaseManagement\Model\Order
     * @return void
     */
    protected function sendApprovalMail($order)
    {
        $wholesaler = $this->wholesaleHelper->getWholesalerData($order->getWholesalerId());
        $data = [
            'var_name' => $wholesaler->getName(),
            'var_message' => __(
                'New purchase order %1 has been approved by Admin.
                                 Please login into your account to check the order details.',
                $order->getIncrementId()
            ),
            'sender_email' => $this->wholesaleHelper->getAdminEmail(),
            'sender_name' => $this->wholesaleHelper->getAdminName(),
            'receiver_name' => $wholesaler->getName(),
            'receiver_mail' => $wholesaler->getEmail(),
            'subject' => __('Purchase Order Approved')
        ];
        $this->helper->sendUpdateStatusMail($data);
    }

    /**
     * Update Ship Status
     *
     * @param collection $model
     * @param int $status
     * @return void
     */
    public function setStatus($model, $status)
    {
        $model->setShipStatus($status);
        $model->setEntityId($model->getEntityId());
        $model->save();
    }
}
