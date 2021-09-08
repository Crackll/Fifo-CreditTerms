<?php
 /**
  * Webkul_MpDailyDeal Deal Link Block.
  * @category  Webkul
  * @package   Webkul_MpDailyDeal
  * @author    Webkul
  * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
  * @license   https://store.webkul.com/license.html
  */
namespace Webkul\MpDailyDeal\Block;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Catalog\Model\Product;

class DealLink extends \Webkul\Marketplace\Block\Sellerblock
{
    /**
     * @var Product
     */
    protected $_product = null;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $session;
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;
    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @return string
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Customer $customer,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $session,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->Customer = $customer;

        $this->_coreRegistry = $registry;
        $this->Session = $session;
        $this->mpHelper = $mpHelper;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $registry, $customer, $session, $mpHelper, $data);
    }
    public function getShopUrl()
    {
        $shopUrl = $this->getRequest()->getParam('shop');
        if ($shopUrl == '') {
            $sellerId = 0;
            $pro = parent::getProduct();
            $helper = $this->_objectManager->create(\Webkul\Marketplace\Helper\Data::class);
            $marketplaceProduct = $helper->getSellerProductDataByProductId($pro['entity_id']);
            foreach ($marketplaceProduct as $value) {
                $sellerId = $value['seller_id'];
            }
            if ($sellerId) {
                $rowsocial = $helper->getSellerDataBySellerId($sellerId);
                foreach ($rowsocial as $value) {
                    $shopUrl = $value['shop_url'];
                }
            }
        }
        return $shopUrl;
    }

    public function geMpHelper()
    {
        return $this->mpHelper;
    }
}
