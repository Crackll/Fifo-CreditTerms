<?php
/**
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPurchaseManagement\Controller\Order;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Helper\Session\CurrentCustomerAddress;

class Create extends Action
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
     * @var CurrentCustomerAddress
     */
    protected $currentCustomerAddress;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Webkul\MpWholesale\Model\QuotesFactory
     */
    protected $quotesFactory;

    /**
     * @var \Webkul\MpPurchaseManagement\Model\OrderItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var \Webkul\MpPurchaseManagement\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var \Webkul\MpWholesale\Helper\Data
     */
    protected $wholesaleHelper;

    /**
     * @var \Webkul\MpPurchaseManagement\Helper\Data
     */
    protected $helper;

    /**
     * @param Context                                             $context
     * @param \Magento\Customer\Model\Session                     $customerSession
     * @param \Magento\Customer\Model\Url                         $customerUrl
     * @param \Magento\Catalog\Model\ProductFactory               $productFactory
     * @param \Webkul\MpWholesale\Model\QuotesFactory             $quotesFactory
     * @param \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory
     * @param \Webkul\MpPurchaseManagement\Model\OrderFactory     $orderFactory
     * @param \Webkul\Marketplace\Helper\Data                     $mpHelper
     * @param \Webkul\MpWholesale\Helper\Data                     $wholesaleHelper
     * @param \Webkul\MpPurchaseManagement\Helper\Data            $helper
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $customerUrl,
        CurrentCustomerAddress $currentCustomerAddress,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\MpWholesale\Model\QuotesFactory $quotesFactory,
        \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory,
        \Webkul\MpPurchaseManagement\Model\OrderFactory $orderFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MpWholesale\Helper\Data $wholesaleHelper,
        \Webkul\MpPurchaseManagement\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->customerUrl = $customerUrl;
        $this->currentCustomerAddress = $currentCustomerAddress;
        $this->productFactory = $productFactory;
        $this->quotesFactory = $quotesFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->orderFactory = $orderFactory;
        $this->mpHelper = $mpHelper;
        $this->wholesaleHelper = $wholesaleHelper;
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
     * create purchase order
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
        $quoteId = $this->getRequest()->getParam('quote_id');
        if (!$quoteId) {
            $this->messageManager->addError(__('Some error occured, Please try again'));
            return $resultRedirect->setPath('mpwholesale/quotation/view');
        }
        if ($this->currentCustomerAddress->getDefaultShippingAddress() === null) {
            $this->messageManager->addError(
                __('Please add default shipping address to create purchase order')
            );
            return $resultRedirect->setPath('customer/address/index');
        }
        $quote = $this->quotesFactory->create()->load($quoteId);
        if ($this->checkConditions($quote)) {
            //create new order
            $order = $this->orderFactory->create();
            if ($this->helper->isOrderApprovalRequired()) {
                $order->setStatus(\Webkul\MpPurchaseManagement\Model\Order::STATUS_NEW);
            } else {
                $order->setStatus(\Webkul\MpPurchaseManagement\Model\Order::STATUS_PROCESSING);
            }
            $order->setWholesalerId($quote->getWholesalerId());
            $order->setSource('Quote #'.$quoteId);
            $order->setIncrementId($this->helper->getNewIncrementId($quoteId));
            $order->setGrandTotal($quote->getQuoteQty()*$quote->getQuotePrice());
            $order->setOrderCurrencyCode($quote->getQuoteCurrencyCode());
            $order->save();

            //add order items
            $product = $this->productFactory->create()->load($quote->getProductId());
            $orderItem = $this->orderItemFactory->create();
            $orderItem->setPurchaseOrderId($order->getEntityId());
            $orderItem->setQuoteId($quoteId);
            $orderItem->setSellerId($quote->getSellerId());
            $orderItem->setProductId($quote->getProductId());
            $orderItem->setSku($product->getSku());
            $orderItem->setQuantity($quote->getQuoteQty());
            $orderItem->setReceivedQty(0);
            $orderItem->setWeight($product->getWeight());
            $orderItem->setShipStatus(\Webkul\MpPurchaseManagement\Model\OrderItem::STATUS_PROCESSING);
            $orderItem->setPrice($quote->getQuotePrice());
            $orderItem->setCurrencyCode($quote->getQuoteCurrencyCode());
            $orderItem->save();

            //send mails
            $this->helper->sendNewPurchaseOrderMail($order, $orderItem);
            if (!$this->helper->isOrderApprovalRequired()) {
                $this->sendApprovalMail($order);
            }
            $this->messageManager->addSuccess(__('Purchase Order successfully created'));
            return $resultRedirect->setPath('mppurchasemanagement/order/view', ['id' => $order->getEntityId()]);
        } else {
            return $resultRedirect->setPath('mpwholesale/quotation/edit', ['id' => $quoteId]);
        }
    }

    /**
     * check the conditions for create purchase order
     * @param  \Webkul\MpWholesale\Model\Quotes
     * @return bool
     */
    protected function checkConditions($quote)
    {
        //check quote ownership
        if ($quote->getSellerId()!= $this->mpHelper->getCustomerId()) {
            return false;
        }

        //check quote status
        if ($quote->getStatus()!=\Webkul\MpWholesale\Model\Quotes::STATUS_APPROVED) {
            $this->messageManager->addError(__('The quote is not approved by the Wholesaler'));
            return false;
        }

        //check whether purchase order is already created for this quote or not
        $collection = $this->orderItemFactory->create()->getCollection()
                           ->addFieldToFilter('quote_id', ['eq'=>$quote->getEntityId()]);
        if ($collection->getSize()>0) {
            $this->messageManager->addError(__('Purchase order is already created for this quote'));
            return false;
        }

        //check whether product exists or not
        $product = $this->productFactory->create()->load($quote->getProductId());
        if ($product->getStatus()!=\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            $this->messageManager->addError(__('Quote product does not exist or disabled'));
            return false;
        }
        return true;
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
}
