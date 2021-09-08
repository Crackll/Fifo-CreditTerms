<?php
/**
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPurchaseManagement\Controller\Adminhtml\Order;

class Ship extends \Webkul\MpPurchaseManagement\Controller\Adminhtml\Order
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
     * purchase order items ship
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getParams();
        if (!isset($data['id']) || !isset($data['date'])) {
            $this->messageManager->addError(__('Some error occured, Please try again'));
            return $resultRedirect->setPath('mppurchasemanagement/order/index');
        } else {
            $today = strtotime(date('Y-m-d'));
            $date = strtotime($data['date']);
            if ($date<$today) {
                $this->messageManager->addError(__('Invalid Date'));
                return $resultRedirect->setPath('mppurchasemanagement/order/view', ['id' => $data['id']]);
            }
        }
        $order = $this->orderFactory->create()->load($data['id']);
        if ($order->getStatus()==\Webkul\MpPurchaseManagement\Model\Order::STATUS_PROCESSING) {
            $order->setStatus(\Webkul\MpPurchaseManagement\Model\Order::STATUS_SHIPPED);
            $order->setEntityId($data['id']);
            $order->save();

            //ship order items
            $customerIds = [];
            $orderItemCollection = $this->orderItemFactory->create()->getCollection()
                                        ->addFieldToFilter('purchase_order_id', ['eq'=>$data['id']]);
            foreach ($orderItemCollection as $item) {
                if (!in_array($item->getSellerId(), $customerIds)) {
                    array_push($customerIds, $item->getSellerId());
                }
                $status = \Webkul\MpPurchaseManagement\Model\OrderItem::STATUS_SHIPPED;
                $this->updateStatusAndDate($item, $status, $data);
            }
            $this->sendMail($customerIds, $order);
            $this->messageManager->addSuccess(__('Purchase Order items successfully shipped'));
        } else {
            $this->messageManager->addError(__('Purchase Order items can not be shipped'));
        }
        return $resultRedirect->setPath('mppurchasemanagement/order/view', ['id' => $order->getEntityId()]);
    }

    /**
     * send item shipped mail
     * @param  array
     * @param  \Webkul\MpPurchaseManagement\Model\Order
     * @return void
     */
    protected function sendMail($customerIds, $order)
    {
        $wholesaler = $this->wholesaleHelper->getWholesalerData($order->getWholesalerId());
        if ($this->helper->isSellerShipmentAllowed()) {
            foreach ($customerIds as $id) {
                $customer = $this->wholesaleHelper->getCustomerData($id);
                $data = [
                    'var_name' => $customer->getName(),
                    'var_message' => __(
                        'Items of purchase order %1 has been shipped by the wholesaler.',
                        $order->getIncrementId()
                    ),
                    'sender_email' => $wholesaler->getEmail(),
                    'sender_name' => $wholesaler->getName(),
                    'receiver_name' => $customer->getName(),
                    'receiver_mail' => $customer->getEmail(),
                    'subject' => __('Purchase Order Items Shipped')
                ];
                $this->helper->sendUpdateStatusMail($data);
            }
        } else {
            $data = [
                'var_name' => __('Admin'),
                'var_message' => __(
                    'Items of purchase order %1 has been shipped by the wholesaler.',
                    $order->getIncrementId()
                ),
                'sender_email' => $wholesaler->getEmail(),
                'sender_name' => $wholesaler->getName(),
                'receiver_name' => $this->wholesaleHelper->getAdminName(),
                'receiver_mail' => $this->wholesaleHelper->getAdminEmail(),
                'subject' => __('Purchase Order Items Shipped')
            ];
            $this->helper->sendUpdateStatusMail($data);
        }
    }

    /**
     * Update Ship Status and Date
     *
     * @param collection $item
     * @param int $status
     * @param array $data
     * @return void
     */
    public function updateStatusAndDate($item, $status, $data)
    {
        $item->setShipStatus($status);
        $item->setScheduleDate($data['date']);
        $item->setEntityId($item->getEntityId());
        $item->save();
    }
}
