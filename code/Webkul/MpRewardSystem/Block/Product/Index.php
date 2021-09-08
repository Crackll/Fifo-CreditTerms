<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRewardSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpRewardSystem\Block\Product;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Session;

class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $currency;

    /** @var \Magento\Catalog\Model\Product */
    protected $productlists;
    /**
     *
     */
    protected $jsonData;
    /**
     *
     */
    protected $rewardHelper;
    /**
     *
     */
    protected $pricingHelper;
    /**
     * @param Context $context
     * @param array   $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Session $customerSession,
        PriceCurrencyInterface $currency,
        \Magento\Framework\Json\Helper\Data $jsonData,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Webkul\MpRewardSystem\Helper\Data $rewardHelper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $mpProductCollection,
        ProductFactory $product,
        array $data = []
    ) {
        $this->objectManager = $objectManager;
        $this->customerSession = $customerSession;
        $this->currency = $currency;
        $this->_mpHelper = $mpHelper;
        $this->jsonData = $jsonData;
        $this->rewardHelper = $rewardHelper;
        $this->pricingHelper = $pricingHelper;
        $this->_eavAttribute = $eavAttribute;
        $this->_mpProductCollection = $mpProductCollection;
        $this->_product = $product;
        parent::__construct($context, $data);
    }

    public function _getCustomerData()
    {
        return $this->customerSession->getCustomer();
    }

    public function getCurrentCurrencySymbol()
    {
        return $this->currency->getCurrencySymbol();
    }

    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getAllProducts()
    {
        $storeId = $this->_mpHelper->getCurrentStoreId();
        $websiteId = $this->_mpHelper->getWebsiteId();
        if (!($customerId = $this->customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->productlists) {
            $paramData = $this->getRequest()->getParams();
            $filter = '';
            $filterStatus = '';
            $filterPointsFrom = '';
            $filterPointsTo = '';
            if (isset($paramData['s'])) {
                $filter = $paramData['s'] != '' ? $paramData['s'] : '';
            }
            if (isset($paramData['status'])) {
                $filterStatus = $paramData['status'] != '' ? $paramData['status'] : '';
            }
            if (isset($paramData['ponits_from'])) {
                $filterPointsFrom = $paramData['ponits_from'] != '' ? $paramData['ponits_from'] : '';
            }
            if (isset($paramData['ponits_to'])) {
                $filterPointsTo = $paramData['ponits_to'] != '' ? $paramData['ponits_to'] : '';
            }
            $proAttId = $this->_eavAttribute->getIdByCode('catalog_product', 'name');

            $catalogProductEntity = $this->_mpProductCollection->create()->getTable('catalog_product_entity');
            $catalogProductEntityVarchar = $this->_mpProductCollection->create()
                                          ->getTable('catalog_product_entity_varchar');

            $catalogProductEntityInt = $this->_mpProductCollection->create()
                                       ->getTable('catalog_product_entity_int');
            $mpRewardProducts = $this->_mpProductCollection->create()->getTable('wk_mp_reward_products');

            /* Get Seller Product Collection for current Store Id */

            $storeCollection = $this->_mpProductCollection->create()
                            ->addFieldToFilter(
                                'seller_id',
                                $customerId
                            )->addFieldToSelect(
                                ['mageproduct_id']
                            );

            $storeCollection->getSelect()->join(
                $catalogProductEntityVarchar.' as cpev',
                'main_table.mageproduct_id = cpev.entity_id'
            )->where(
                'cpev.store_id = '.$storeId.' AND
                cpev.value like "%'.$filter.'%" AND
                cpev.attribute_id = '.$proAttId
            );

            $storeCollection->getSelect()->join(
                $catalogProductEntityInt.' as cpei',
                'main_table.mageproduct_id = cpei.entity_id'
            )->where(
                'cpei.store_id = '.$storeId
            );

            $storeCollection->getSelect()->join(
                $catalogProductEntity.' as cpe',
                'main_table.mageproduct_id = cpe.entity_id'
            );

            $storeCollection->getSelect()->group('mageproduct_id');

            $storeProductIDs = $storeCollection->getAllIds();

            /* Get Seller Product Collection for 0 Store Id */

            $adminStoreCollection = $this->_mpProductCollection->create();

            $adminStoreCollection->addFieldToFilter(
                'seller_id',
                $customerId
            )->addFieldToSelect(
                ['mageproduct_id']
            );

            $adminStoreCollection->getSelect()->join(
                $catalogProductEntityVarchar.' as cpev',
                'main_table.mageproduct_id = cpev.entity_id'
            )->where(
                'cpev.store_id = 0 AND
                cpev.value like "%'.$filter.'%" AND
                cpev.attribute_id = '.$proAttId
            );

            $adminStoreCollection->getSelect()->join(
                $catalogProductEntityInt.' as cpei',
                'main_table.mageproduct_id = cpei.entity_id'
            )->where(
                'cpei.store_id = 0'
            );

            $adminStoreCollection->getSelect()->join(
                $catalogProductEntity.' as cpe',
                'main_table.mageproduct_id = cpe.entity_id'
            );

            $adminStoreCollection->getSelect()->group('mageproduct_id');

            $adminProductIDs = $adminStoreCollection->getAllIds();

            $productIDs = array_merge($storeProductIDs, $adminProductIDs);

            $collection = $this->_mpProductCollection->create()
                        ->addFieldToFilter(
                            'mageproduct_id',
                            ['in' => $productIDs]
                        );

            $collection->getSelect()->joinLeft(
                $mpRewardProducts.' as sconf',
                "main_table.mageproduct_id = sconf.product_id",
                [
                  "points" => "points",
                  "reward_status" => "status"
                ]
            );
            $collection->getSelect()->where(
                'main_table.seller_id = '.$customerId
            );
            if ($filterPointsFrom != '' && $filterPointsTo != '') {
                $collection->getSelect()->where(
                    "sconf.points BETWEEN '".$filterPointsFrom."' AND '".$filterPointsTo."'"
                );
            }
            if ($filterStatus) {
                $collection->getSelect()->where(
                    'sconf.status = '.$filterStatus
                );
            }
            $collection->setOrder('mageproduct_id');
            $this->productlists = $collection;
        }

        return $this->productlists;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllProducts()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'mpreward.product.list.pager'
            )->setCollection(
                $this->getAllProducts()
            );
            $this->setChild('pager', $pager);
            $this->getAllProducts()->load();
        }
       
        return $this;
    }

   /**
    * @return string
    */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getProductData($id = '')
    {
        return $this->_product->create()->load($id);
    }
    /**
     * @return json Data
     */
    public function getJsonData()
    {
        return $this->jsonData;
    }
    /**
     * @return marketplace helper
     */
    public function getMarketplaceHelper()
    {
        return $this->_mpHelper;
    }
    /**
     * @return reward helper
     */
    public function getRewardHelper()
    {
        return $this->rewardHelper;
    }
    /**
     * @return princing helper
     */
    public function getPricingHelper()
    {
        return $this->pricingHelper;
    }
}
