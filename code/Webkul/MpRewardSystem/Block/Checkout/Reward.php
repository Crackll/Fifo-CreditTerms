<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRewardSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpRewardSystem\Block\Checkout;

use Magento\Customer\Model\CustomerFactory;
use Webkul\Marketplace\Block\Product\ProductlistFactory;
use Magento\Framework\Session\SessionManager;

class Reward extends \Magento\Framework\View\Element\Template
{
    /**
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var Magento\Customer\Model\Customer
     */
    protected $customer;
    /**
     * @var Webkul\Marketplace\Block\Product\Productlist
     */
    protected $marketplaceProduct;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;
    /**
     *
     */
    protected $pricingHelper;
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        SessionManager $session,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        CustomerFactory $customer,
        ProductlistFactory $marketplaceProduct,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Checkout\Model\Session $cart,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MpRewardSystem\Model\RewardrecordFactory $rewardRecord,
        \Webkul\MpRewardSystem\Helper\Data $mpRewardHelper,
        array $data = []
    ) {
        $this->objectManager = $objectManager;
        $this->customerSession = $customerSession;
        $this->session = $session;
        $this->productFactory = $productFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->customer = $customer;
        $this->marketplaceProduct = $marketplaceProduct;
        $this->pricingHelper = $pricingHelper;
        $this->_cart = $cart;
        $this->_mpHelper = $mpHelper;
        $this->rewardRecord = $rewardRecord;
        $this->mpRewardHelper = $mpRewardHelper;
        parent::__construct($context, $data);
    }
    /**
     * get Current customer loggedin
     * @return \Magento\Customer\Model\Session
     */
    public function isCustomerLoggedin()
    {
        return $this->customerSession->isLoggedIn();
    }
    /**
     * get Current customer from session
     * @return \Magento\Customer\Model\Session
     */
    public function _getCustomerData()
    {
        return $this->customerSession->getCustomer();
    }
    /**
     * get marketplace product collection
     * @return \Webkul\Marketplace\Block\Product\Productlist
     */
    public function getProdultList()
    {
        return $this->marketplaceProduct->create()->getAllProducts();
    }
    /**
     * @param  int $productId
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductDetails($productId)
    {
        $model = $this->productFactory->create()->load($productId);
        return $model;
    }
    /**
     * reward list
     * @return array
     */
    public function getRewards()
    {
        $customerId = $this->customerSession->getId();
        $cart = $this->_cart->getQuote();
        $sellerIds = [];
        foreach ($cart->getAllVisibleItems() as $item) {
            $productId = $item->getProduct()->getId();
            $model = $this->_mpHelper->getSellerProductDataByProductId($productId);
            if ($model->getSize()) {
                foreach ($model as $value) {
                    $sellerIds[] = $value->getSellerId();
                }
            } else {
                $sellerIds[] = 0;
            }
        }
        $options = [];
        $collection = $this->rewardRecord->create()
                      ->getCollection()
                      ->addFieldToFilter('customer_id', ['eq' => $customerId])
                      ->addFieldToFilter('seller_id', ['in' => $sellerIds]);
        foreach ($collection as $info) {
            if (!isset($options[$info->getSellerId()])) {
                $options[$info->getSellerId()] = [];
                $remainingRewards = $info->getRemainingRewardPoint();
            } else {
                $remainingRewards = $options[$info->getSellerId()]['remaining_reward_point']
                                   + $info->getRemainingRewardPoint();
            }
            $options[$info->getSellerId()]['remaining_reward_point'] = $remainingRewards;
            $options[$info->getSellerId()]['amount'] = $remainingRewards * $this->mpRewardHelper
                                                       ->getRewardValue($info->getSellerId());
        }
        return $options;
    }

    public function getSellerName($sellerId = 0)
    {
        if ($sellerId) {
            return $this->customer->create()->load($sellerId)->getName();
        } else {
            return "Admin";
        }
    }
    /**
     * list of current applied reward
     * @return array
     */
    public function getRewardsSession()
    {
        return $this->session->getRewardInfo();
    }
    /**
     * @return princing helper
     */
    public function getPricingHelper()
    {
        return $this->pricingHelper;
    }
}
