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

class MassConfirm extends \Webkul\MpPurchaseManagement\Controller\Adminhtml\Order
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
     * @var \Webkul\MpWholesale\Helper\Data
     */
    protected $wholesaleHelper;

    /**
     * @var \Webkul\MpPurchaseManagement\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Backend\App\Action\Context                 $context
     * @param \Magento\Ui\Component\MassAction\Filter             $filter
     * @param \Webkul\MpPurchaseManagement\Model\OrderFactory     $orderFactory
     * @param \Webkul\MpWholesale\Helper\Data                     $wholesaleHelper
     * @param \Webkul\MpPurchaseManagement\Helper\Data            $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Webkul\MpPurchaseManagement\Model\OrderFactory $orderFactory,
        \Webkul\MpWholesale\Helper\Data $wholesaleHelper,
        \Webkul\MpPurchaseManagement\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->orderFactory = $orderFactory;
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
        foreach ($collection as $order) {
            if ($order->getStatus()==\Webkul\MpPurchaseManagement\Model\Order::STATUS_NEW) {
                $status = \Webkul\MpPurchaseManagement\Model\Order::STATUS_PROCESSING;
                $this->setStatus($order, $status);
                $confirmCount++;
                $this->sendApprovalMail($order);
            } else {
                $notConfirmCount++;
            }
        }
        if ($confirmCount>0) {
            $this->messageManager->addSuccess(__('%1 order(s) Confirmed', $confirmCount));
        }
        if ($notConfirmCount>0) {
            $this->messageManager->addError(__('%1 order(s) could not be Confirmed', $notConfirmCount));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
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
      * Update Status
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
}
