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

class OrderList extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezoneInterface;

    /**
     * @var orderCollection
     */
    protected $_orderCollection;

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
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface  $timezoneInterface
     * @param \Webkul\MpPurchaseManagement\Model\OrderItemFactory   $orderItemFactory
     * @param \Webkul\MpPurchaseManagement\Model\OrderFactory       $orderFactory
     * @param \Webkul\MpWholesale\Model\WholeSaleUserFactory        $wholesalerFactory
     * @param \Webkul\Marketplace\Helper\Data                       $mpHelper
     * @param \Webkul\MpPurchaseManagement\Model\Source\OrderStatus $orderStatus
     * @param array                                                 $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory,
        \Webkul\MpPurchaseManagement\Model\OrderFactory $orderFactory,
        \Webkul\MpWholesale\Model\WholeSaleUserFactory $wholesalerFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MpPurchaseManagement\Model\Source\OrderStatus $orderStatus,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->timezoneInterface = $timezoneInterface;
        $this->orderItemFactory = $orderItemFactory;
        $this->orderFactory = $orderFactory;
        $this->wholesalerFactory = $wholesalerFactory;
        $this->mpHelper = $mpHelper;
        $this->orderStatus = $orderStatus;
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getOrderCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'purchase-order.pager'
            )
            ->setCollection(
                $this->getOrderCollection()
            );
            $this->setChild('pager', $pager);
            $this->getOrderCollection()->load();
        }
        return $this;
    }

    /**
     * get order collection for current user
     * @return \Webkul\MpPurchaseManagement\Model\ResourceModel\Order\Collection
     */
    public function getOrderCollection()
    {
        if (!$this->_orderCollection) {
            $customerId = $this->mpHelper->getCustomerId();
            $orderItems = $this->orderItemFactory->create()->getCollection()
                               ->addFieldToFilter('seller_id', ['eq'=>$customerId]);
            $orderIds = [];
            foreach ($orderItems as $item) {
                if (!in_array($item->getPurchaseOrderId(), $orderIds)) {
                    array_push($orderIds, $item->getPurchaseOrderId());
                }
            }
            $this->_orderCollection = $this->orderFactory->create()->getCollection()
                                           ->addFieldToFilter('entity_id', ['in'=>$orderIds]);
        }
        return $this->_orderCollection;
    }

    /**
     * get wholesaler title
     * @param  int
     * @return string
     */
    public function getWholesalerTitle($wholesaler_id)
    {
        return $this->wholesalerFactory->create()->getCollection()
                    ->addFieldToFilter('user_id', ['eq'=>$wholesaler_id])
                    ->setPageSize(1)->getFirstItem()->getTitle();
    }

    /**
     * get order total for particular seller
     * @param  int
     * @return float
     */
    public function getTotal($orderId)
    {
        $customerId = $this->mpHelper->getCustomerId();
        $itemCollection = $this->orderItemFactory->create()->getCollection()
                               ->addFieldToFilter('seller_id', ['eq'=>$customerId])
                               ->addFieldToFilter('purchase_order_id', ['eq'=>$orderId]);
        $total = 0;
        foreach ($itemCollection as $item) {
            $total = $total + $item->getQuantity()*$item->getPrice();
        }
        return $total;
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
     * get date according to time zone
     * @param  string
     * @return string
     */
    public function getDateTime($date)
    {
        return $this->timezoneInterface->date(new \DateTime($date))->format('m/d/y H:i:s');
    }

    /**
     * get order view url
     * @param  int
     * @return string
     */
    public function getViewUrl($orderId)
    {
        return $this->getUrl(
            'mppurchasemanagement/order/view',
            ['_secure' => $this->getRequest()->isSecure(),'id' => $orderId]
        );
    }

    /**
     * pager on table footer
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
