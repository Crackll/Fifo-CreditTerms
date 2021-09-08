<?php
/**
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPurchaseManagement\Controller\Order;

use \Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;

class PrintAction extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var Magento\Customer\Model\Url
     */
    protected $customerUrl;
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
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var \Webkul\MpPurchaseManagement\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\MpPurchaseManagement\Model\ResourceModel\OrderItem\Collection
     */
    protected $orderItemCollection;

    /**
     * @param Context                                             $context
     * @param \Magento\Customer\Model\Session                     $customerSession
     * @param \Magento\Customer\Model\Url                         $customerUrl
     * @param \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory
     * @param \Webkul\MpPurchaseManagement\Model\OrderFactory     $orderFactory
     * @param \Webkul\MpPurchaseManagement\Model\Pdf\Shipment     $pdfShipment
     * @param \Magento\Framework\Stdlib\DateTime\DateTime         $dateTime
     * @param \Magento\Framework\App\Response\Http\FileFactory    $fileFactory
     * @param \Webkul\Marketplace\Helper\Data                     $mpHelper
     * @param \Webkul\MpPurchaseManagement\Helper\Data            $helper
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $customerUrl,
        \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory,
        \Webkul\MpPurchaseManagement\Model\OrderFactory $orderFactory,
        \Webkul\MpPurchaseManagement\Model\Pdf\Shipment $pdfShipment,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MpPurchaseManagement\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->customerUrl = $customerUrl;
        $this->orderItemFactory = $orderItemFactory;
        $this->orderFactory = $orderFactory;
        $this->pdfShipment = $pdfShipment;
        $this->dateTime = $dateTime;
        $this->fileFactory = $fileFactory;
        $this->mpHelper = $mpHelper;
        $this->helper = $helper;
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->customerUrl->getLoginUrl();
        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * purchase order update status
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->helper->getModuleStatus()) {
            $this->messageManager->addError(__('Module is disabled by admin, Please contact to admin!"'));
            return $resultRedirect->setPath('customer/account', ['_secure'=>$this->getRequest()->isSecure()]);
        }
        $orderId = $this->getRequest()->getParam('id');
        if (!$orderId) {
            $this->messageManager->addError(__('Some error occured, Please try again'));
            return $resultRedirect->setPath('mppurchasemanagement/order/index');
        }
        if ($this->checkConditions()) {
            try {
                $pdf = $this->pdfShipment->getPdf($this->getItemCollection()->getAllIds());
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
        }
        return $resultRedirect->setPath('mppurchasemanagement/order/view', ['id' => $orderId]);
    }

    /**
     * get order item collection
     * @return \Webkul\MpPurchaseManagement\Model\ResourceModel\OrderItem\Collection
     */
    protected function getItemCollection()
    {
        if (!$this->orderItemCollection) {
            $this->orderItemCollection = $this->orderItemFactory->create()->getCollection()
                           ->addFieldToFilter('purchase_order_id', ['eq'=>$this->getRequest()->getParam('id')])
                           ->addFieldToFilter('seller_id', ['eq'=>$this->mpHelper->getCustomerId()]);
        }
        return $this->orderItemCollection;
    }

    /**
     * check the conditions for receiving purchase order Shipments
     * @return bool
     */
    protected function checkConditions()
    {
        //check order ownership
        $collection = $this->getItemCollection();
        if ($collection->getSize()==0) {
            return false;
        }

        $order = $this->orderFactory->create()->load($this->getRequest()->getParam('id'));
        //check whether order exist or not
        if (!$order->getEntityId()) {
            return false;
        }

        //check Allow seller config for receiving Shipment
        if (!$this->helper->isSellerShipmentAllowed()) {
            $this->messageManager->addError(__('Admin has not allowed Seller to print shipments'));
            return false;
        }
        
        return true;
    }
}
