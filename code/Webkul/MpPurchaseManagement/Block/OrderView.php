<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPurchaseManagement\Block;

class OrderView extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

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
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var \Webkul\MpPurchaseManagement\Model\Source\OrderStatus
     */
    protected $orderStatus;

    /**
     * @param \Magento\Catalog\Block\Product\Context                $context
     * @param \Magento\Catalog\Model\Product\Factory                $productFactory
     * @param \Webkul\MpPurchaseManagement\Model\OrderItemFactory    $orderItemFactory
     * @param \Webkul\MpPurchaseManagement\Model\OrderFactory        $orderFactory
     * @param \Webkul\MpWholesale\Model\WholeSaleUserFactory        $wholesalerFactory
     * @param \Webkul\Marketplace\Helper\Data                       $mpHelper
     * @param \Webkul\MpPurchaseManagement\Model\Source\OrderStatus $orderStatus
     * @param array                                                 $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory,
        \Webkul\MpPurchaseManagement\Model\OrderFactory $orderFactory,
        \Webkul\MpWholesale\Model\WholeSaleUserFactory $wholesalerFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MpPurchaseManagement\Model\Source\OrderStatus $orderStatus,
        \Webkul\MpPurchaseManagement\Helper\Data $mpPurchaseHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productFactory = $productFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->orderFactory = $orderFactory;
        $this->wholesalerFactory = $wholesalerFactory;
        $this->mpHelper = $mpHelper;
        $this->orderStatus = $orderStatus;
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
                    ->addFieldToFilter('purchase_order_id', ['eq'=>$this->getOrderId()])
                    ->addFieldToFilter('seller_id', ['eq'=>$this->mpHelper->getCustomerId()]);
    }

    /**
     * get product name
     * @param  int
     * @return string
     */
    public function getProductName($product_id)
    {
        return $this->productFactory->create()->load($product_id)->getName();
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
     * get cancel purchase order url
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->getUrl(
            'mppurchasemanagement/order/cancel',
            ['_secure' => $this->getRequest()->isSecure(),'id' => $this->getOrderId()]
        );
    }

    /**
     * get receive shipment for purchase order url
     * @return string
     */
    public function getReceiveShipmentUrl()
    {
        return $this->getUrl(
            'mppurchasemanagement/order/receive',
            ['_secure' => $this->getRequest()->isSecure(),'id' => $this->getOrderId()]
        );
    }

    /**
     * get print shipment for purchase order url
     * @return string
     */
    public function getPrintShipmentUrl()
    {
        return $this->getUrl(
            'mppurchasemanagement/order/print',
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
