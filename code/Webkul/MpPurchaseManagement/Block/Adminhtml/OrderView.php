<?php
/**
 *
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPurchaseManagement\Block\Adminhtml;

class OrderView extends \Magento\Framework\View\Element\Template
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
     * @var \Webkul\MpWholesale\Model\WholeSaleUserFactory
     */
    protected $wholesalerFactory;

    /**
     * @var \Webkul\MpPurchaseManagement\Model\Source\OrderStatus
     */
    protected $orderStatus;

    /**
     * @var \Webkul\MpWholesale\Helper\Data
     */
    protected $wholesalerHelper;

    /**
     * @param \Magento\Catalog\Block\Product\Context                $context
     * @param \Webkul\MpPurchaseManagement\Model\OrderItemFactory   $orderItemFactory
     * @param \Webkul\MpPurchaseManagement\ModelOrderFactory        $orderFactory
     * @param \Webkul\MpWholesale\Model\WholeSaleUserFactory        $wholesalerFactory
     * @param \Webkul\MpPurchaseManagement\Model\Source\OrderStatus $orderStatus
     * @param \Webkul\MpWholesale\Helper\Data                       $wholesalerHelper
     * @param array                                                 $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory,
        \Webkul\MpPurchaseManagement\Model\OrderFactory $orderFactory,
        \Webkul\MpWholesale\Model\WholeSaleUserFactory $wholesalerFactory,
        \Webkul\MpPurchaseManagement\Model\Source\OrderStatus $orderStatus,
        \Webkul\MpWholesale\Helper\Data $wholesalerHelper,
        \Webkul\MpPurchaseManagement\Helper\Data $mpPurchaseHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderItemFactory = $orderItemFactory;
        $this->orderFactory = $orderFactory;
        $this->wholesalerFactory = $wholesalerFactory;
        $this->orderStatus = $orderStatus;
        $this->wholesalerHelper = $wholesalerHelper;
        $this->mpPurchaseHelper = $mpPurchaseHelper;
    }

    /**
     * get order ID
     * @return int
     */
    public function getOrderId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * get order
     * @return \Webkul\MpPurchaseManagement\Model\OrderFactory
     */
    public function getOrder()
    {
        return $this->orderFactory->create()->load($this->getOrderId());
    }

    /**
     * get order item collection
     * @return \Webkul\MpPurchaseManagement\Model\ResourceModel\Order\Collection
     */
    public function getItemCollection()
    {
        return $this->orderItemFactory->create()->getCollection()
                    ->addFieldToFilter('purchase_order_id', ['eq'=>$this->getOrderId()]);
    }

    /**
     * get product name
     * @param  int
     * @return string
     */
    public function getProductName($product_id)
    {
        return $this->wholesalerHelper->getProduct($product_id)->getName();
    }

    /**
     * get order status label
     * @param  int
     * @return string
     */
    public function getOrderStatusLabel($status)
    {
        $optionArray = $this->orderStatus->getOptionArray();
        return $optionArray[$status];
    }

    /**
     * get wholesaler details
     * @param  int
     * @return \Webkul\MpWholesale\Model\WholeSaleUser
     */
    public function getWholesalerDetails($wholesaler_id)
    {
        return $this->wholesalerFactory->create()->getCollection()
                    ->addFieldToFilter('user_id', ['eq'=>$wholesaler_id])
                    ->setPageSize(1)->getFirstItem();
    }

    /**
     * get wholesaler user
     * @param  int
     * @return object
     */
    public function getWholesalerUser($wholesalerId)
    {
        return $this->wholesalerHelper->getWholesalerData($wholesalerId);
    }

    /**
     * get ship purchase order items url
     * @return string
     */
    public function getShipmentUrl()
    {
        return $this->getUrl(
            'mppurchasemanagement/order/ship',
            ['_secure' => $this->getRequest()->isSecure(),'id' => $this->getOrderId()]
        );
    }

    /**
     * Get MpPurchase Helper Object
     *
     * @return object
     */
    public function getMpPurchaseHelper()
    {
        return $this->mpPurchaseHelper;
    }
}
