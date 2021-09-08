<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Block\Account;

use Webkul\Marketplace\Model\ProductFactory;
use Webkul\Marketplace\Model\OrdersFactory;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;
use Webkul\Marketplace\Model\SellertransactionFactory;
use Webkul\Marketplace\Helper\Data as MpHelper;

class Navigation extends \Webkul\Marketplace\Block\Account\Navigation
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var int
     */
    protected $orderId;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var OrdersFactory
     */
    protected $ordersFactory;

    /**
     * @var CollectionFactory
     */
    protected $productCollection;

    /**
     * @var SellertransactionFactory
     */
    protected $sellertransaction;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productModel;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderModel;

    /**
     * @var \Webkul\Marketplace\Model\SaleslistFactory
     */
    protected $saleslistModel;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shipconfig;

    /**
     * @var \Magento\Payment\Model\Config
     */
    protected $paymentConfig;

    /**
     * @var MpHelper
     */
    protected $mpHelper;

    /**
     * @var \Webkul\MpWholesale\Helper\Data
     */
    protected $wholeSaleHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Customer\Model\Session $customerSession
     * @param ProductFactory $productFactory
     * @param OrdersFactory $ordersFactory
     * @param CollectionFactory $productCollection
     * @param SellertransactionFactory $sellertransaction
     * @param \Magento\Catalog\Model\ProductFactory $productModel
     * @param \Magento\Sales\Model\OrderFactory $orderModel
     * @param \Webkul\Marketplace\Model\SaleslistFactory $saleslistModel
     * @param \Magento\Shipping\Model\Config $shipconfig
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param MpHelper $mpHelper
     * @param \Webkul\MpWholesale\Helper\Data $wholeSaleHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Customer\Model\Session $customerSession,
        ProductFactory $productFactory,
        OrdersFactory $ordersFactory,
        CollectionFactory $productCollection,
        SellertransactionFactory $sellertransaction,
        \Magento\Catalog\Model\ProductFactory $productModel,
        \Magento\Sales\Model\OrderFactory $orderModel,
        \Webkul\Marketplace\Model\SaleslistFactory $saleslistModel,
        \Magento\Shipping\Model\Config $shipconfig,
        \Magento\Payment\Model\Config $paymentConfig,
        MpHelper $mpHelper,
        \Webkul\MpWholesale\Helper\Data $wholeSaleHelper,
        array $data = []
    ) {
        $this->mpHelper = $mpHelper;
        $this->wholeSaleHelper = $wholeSaleHelper;
        parent::__construct(
            $context,
            $date,
            $customerSession,
            $productFactory,
            $ordersFactory,
            $productCollection,
            $sellertransaction,
            $productModel,
            $orderModel,
            $saleslistModel,
            $shipconfig,
            $paymentConfig,
            $mpHelper,
            $data
        );
    }

    /**
     * Mp WholeSale Helper Object
     *
     * @return object
     */
    public function getMpWholeSaleHelper()
    {
        return $this->wholeSaleHelper;
    }
    
    /**
     * Get Product View URL
     *
     * @return string
     */
    public function getProductViewUrl()
    {
        return $this->getUrl('mpwholesale/product/view', [
            '_secure' => $this->getRequest()->isSecure()
        ]);
    }
    
    /**
     * Get Quotation View URL
     *
     * @return string
     */
    public function getQuotationViewUrl()
    {
        return $this->getUrl('mpwholesale/quotation/view', [
            '_secure' => $this->getRequest()->isSecure()
        ]);
    }
}
