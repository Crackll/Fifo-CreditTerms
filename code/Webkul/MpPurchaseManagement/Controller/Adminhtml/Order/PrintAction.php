<?php
/**
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPurchaseManagement\Controller\Adminhtml\Order;

use \Magento\Framework\App\Filesystem\DirectoryList;

class PrintAction extends \Webkul\MpPurchaseManagement\Controller\Adminhtml\Order
{
    /**
     * @var \Webkul\MpPurchaseManagement\Model\OrderItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var \Webkul\MpPurchaseManagement\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Webkul\MpPurchaseManagement\Model\Pdf\Shipment
     */
    protected $pdfShipment;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context                 $context
     * @param \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory
     * @param \Webkul\MpPurchaseManagement\Model\OrderFactory     $orderFactory
     * @param \Webkul\MpPurchaseManagement\Model\Pdf\Shipment     $pdfShipment
     * @param \Magento\Framework\Stdlib\DateTime\DateTime         $dateTime
     * @param \Magento\Framework\App\Response\Http\FileFactory    $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory,
        \Webkul\MpPurchaseManagement\Model\OrderFactory $orderFactory,
        \Webkul\MpPurchaseManagement\Model\Pdf\Shipment $pdfShipment,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->orderItemFactory = $orderItemFactory;
        $this->orderFactory = $orderFactory;
        $this->pdfShipment = $pdfShipment;
        $this->dateTime = $dateTime;
        $this->fileFactory = $fileFactory;
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
            try {
                $collection = $this->orderItemFactory->create()->getCollection()
                    ->addFieldToFilter('purchase_order_id', ['eq'=>$orderId]);
                $pdf = $this->pdfShipment->getPdf($collection->getAllIds());
                $date = $this->dateTime->date('Y-m-d_H-i-s');
                return $this->fileFactory->create(
                    'shipment' . $date . '.pdf',
                    $pdf->render(),
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addError(__('Purchase Order Shipment can not be printed'));
        }
        return $resultRedirect->setPath('mppurchasemanagement/order/view', ['id' => $order->getEntityId()]);
    }
}
