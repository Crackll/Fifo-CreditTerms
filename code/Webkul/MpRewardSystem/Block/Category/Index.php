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

namespace Webkul\MpRewardSystem\Block\Category;

use Webkul\MpRewardSystem\Model\CategoryFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Webkul\MpRewardSystem\Model\ResourceModel\Rewardcategory\CollectionFactory as RewardCategoryCollection;
use Magento\Catalog\Helper\Category;

class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $currency;
    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $categorylists;
    /**
     *
     */
    protected $mpHelper;
    /**
     *
     */
    protected $eavAttribute;
    /**
     *
     */
    protected $categoryFactory;
    /**
     *
     */
    protected $rewardCategoryCollection;
    /**
     *
     */
    protected $categoryHelper;
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
        \Magento\Customer\Model\Session $customerSession,
        PriceCurrencyInterface $currency,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Framework\Json\Helper\Data $jsonData,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Webkul\MpRewardSystem\Helper\Data $rewardHelper,
        CategoryFactory $categoryFactory,
        RewardCategoryCollection $rewardCategoryCollection,
        Category $categoryHelper,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->currency = $currency;
        $this->mpHelper = $mpHelper;
        $this->jsonData = $jsonData;
        $this->rewardHelper = $rewardHelper;
        $this->pricingHelper = $pricingHelper;
        $this->eavAttribute = $eavAttribute;
        $this->categoryFactory = $categoryFactory;
        $this->rewardCategoryCollection = $rewardCategoryCollection;
        $this->categoryHelepr = $categoryHelper;
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

    public function getAllCategoryList()
    {
        if (!($customerId = $this->customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->categorylists) {
            $paramData = $this->getRequest()->getParams();
            $filterId = '';
            $filter = '';
            $filterStatus = '';
            $filterPointsFrom = '';
            $filterPointsTo = '';
            if (isset($paramData['fid'])) {
                $filterId = $paramData['fid'] != '' ? $paramData['fid'] : '';
            }
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
            $collection = $this->categoryFactory->create()->getCollection();
            $proAttrId = $this->eavAttribute->getIdByCode("catalog_category", "name");

            $collection->getSelect()->joinLeft(
                ['cpev'=>$collection->getTable('catalog_category_entity_varchar')],
                'main_table.entity_id = cpev.entity_id',
                ['category_name'=>'value']
            )->where(
                'cpev.store_id = 0 AND
                cpev.value like "%'.$filter.'%" AND
                cpev.attribute_id = '.$proAttrId
            );

            $collection->getSelect()->joinLeft(
                ['rc'=>$collection->getTable('wk_mp_reward_category')],
                'main_table.entity_id = rc.category_id AND rc.seller_id ='.$customerId,
                ['points'=>'points',"status"=>'status']
            );

            $collection->addFilterToMap("category_name", "cpev.value");
            $collection->addFilterToMap("points", "rc.points");
            $collection->addFilterToMap("status", "rc.status");
            if ($filterId) {
                $collection->getSelect()->where(
                    'main_table.entity_id = '.$filterId
                );
            }
            if ($filterPointsFrom != '' && $filterPointsTo != '') {
                $collection->getSelect()->where(
                    "rc.points BETWEEN '".$filterPointsFrom."' AND '".$filterPointsTo."'"
                );
            }
            if ($filterStatus) {
                $collection->getSelect()->where(
                    'rc.status = '.$filterStatus
                );
            }
            $this->categorylists = $collection;
        }
        return $this->categorylists;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllCategoryList()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'mpreward.category.list.pager'
            )->setCollection(
                $this->getAllCategoryList()
            );
            $this->setChild('pager', $pager);
            $this->getAllCategoryList()->load();
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
        return $this->mpHelper;
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
