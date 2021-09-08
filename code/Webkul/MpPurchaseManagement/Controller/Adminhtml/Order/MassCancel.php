<?php
/**
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPurchaseManagement\Controller\Adminhtml\Order;

use Magento\Framework\Controller\ResultFactory;

class MassCancel extends \Webkul\MpPurchaseManagement\Controller\Adminhtml\Order
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Webkul\MpPurchaseManagement\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @param \Magento\Backend\App\Action\Context                 $context
     * @param \Magento\Ui\Component\MassAction\Filter             $filter
     * @param \Webkul\MpPurchaseManagement\Model\OrderFactory     $orderFactory
     * @param \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory
     * @param \Webkul\MpWholesale\Helper\Data                     $wholesaleHelper
     * @param \Webkul\MpPurchaseManagement\Helper\Data            $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Webkul\MpPurchaseManagement\Model\OrderFactory $orderFactory,
        \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory,
        \Webkul\MpWholesale\Helper\Data $wholesaleHelper,
        \Webkul\MpPurchaseManagement\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->filter = $filter;
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
        $collection = $this->filter->getCollection($this->orderFactory->create()->getCollection());
        $confirmCount = 0;
        $notConfirmCount = 0;
        $customerIds = [];
        foreach ($collection as $order) {
            if ($order->getStatus()==\Webkul\MpPurchaseManagement\Model\Order::STATUS_NEW) {
                $orderStatus = \Webkul\MpPurchaseManagement\Model\Order::STATUS_CANCELLED;
                $this->setStatus($order, $orderStatus);

                //cancel order items
                $orderItemCollection = $this->orderItemFactory->create()->getCollection()
                                            ->addFieldToFilter('purchase_order_id', ['eq'=>$orderId]);
                foreach ($orderItemCollection as $item) {
                    $itemId = $item->getEntityId();
                    if (!in_array($item->getSellerId(), $customerIds)) {
                        array_push($customerIds, $item->getSellerId());
                    }
                    $status = \Webkul\MpPurchaseManagement\Model\OrderItem::STATUS_CANCELLED;
                    $this->setShipStatus($item, $status);
                }
                $this->sendCancelMail($customerIds, $order);
                $confirmCount++;
            } else {
                $notConfirmCount++;
            }
        }
        if ($confirmCount>0) {
            $this->messageManager->addSuccess(__('%1 order(s) Cancelled', $confirmCount));
        }
        if ($notConfirmCount>0) {
            $this->messageManager->addError(__('%1 order(s) could not be Cancelled', $notConfirmCount));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
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
     * Update PO Status
     *
     * @param collection $order
     * @param int $status
     * @return void
     */
    public function setStatus($order, $status)
    {
        $order->setStatus($status);
        $order->setEntityId($order->getEntityId());
        $order->save();
    }

    /**
     * Update OrderItem Ship Status
     *
     * @param collection $item
     * @param int $status
     * @return void
     */
    public function setShipStatus($item, $status)
    {
        $item->setShipStatus($status);
        $item->setEntityId($item->getEntityId());
        $item->save();
    }
}
