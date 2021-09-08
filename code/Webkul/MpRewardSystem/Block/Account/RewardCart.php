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

namespace Webkul\MpRewardSystem\Block\Account;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\MpRewardSystem\Model\RewardcartFactory;

class RewardCart extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $postDataHelper;
    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;
    /**
     * @var RewardcartFactory
     */
    protected $rewardcartFactory;
     /**
      * @var Webkul\Marketplace\Helper\Data
      */
    protected $marketplaceHelper;
    /**
     * @var Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;
    /**
     * @var Magento\Framework\Json\Helper\Data
     */
    protected $jsonData;

    /**
     * @param \Magento\Catalog\Block\Product\Context            $context
     * @param \Magento\Framework\Data\Helper\PostHelper         $postDataHelper
     * @param \Magento\Framework\Url\Helper\Data                $urlHelper
     * @param Customer                                          $customer
     * @param RewardcartFactory                                 $rewardcartFactory
     * @param \Webkul\MpRewardSystem\Helper\Data                $mpRewardHelper
     * @param \Webkul\Marketplace\Helper\Data                   $marketplaceHelper
     * @param Magento\Framework\Pricing\Helper\Data             $pricingHelper
     * @param \Magento\Customer\Model\Session                   $session
     * @param \Magento\Framework\Locale\CurrencyInterface       $localeCurrency
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array                                             $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        Customer $customer,
        RewardcartFactory $rewardcartFactory,
        \Magento\Framework\Json\Helper\Data $jsonData,
        \Webkul\MpRewardSystem\Helper\Data $mpRewardHelper,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->postDataHelper = $postDataHelper;
        $this->urlHelper = $urlHelper;
        $this->customer = $customer;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->pricingHelper = $pricingHelper;
        $this->rewardcartFactory = $rewardcartFactory;
        $this->jsonData = $jsonData;
        $this->mpRewardHelper = $mpRewardHelper;
        $this->session = $session;
        $this->_localeCurrency = $localeCurrency;
        $this->_priceCurrency = $priceCurrency;
    }
    /**
     * @return reward collection rule
     */
    public function getCartRuleCollection()
    {
        $paramData = $this->getRequest()->getParams();
        $filterType = '';
        $filterPriceFrom = '';
        $filterPriceTo = '';
        $filterDateFrom = '';
        $filterDateTo = '';
        if (isset($paramData['type'])) {
            $filterType = $paramData['type'] != '' ? $paramData['type'] : '';
        }
        if (isset($paramData['from_date'])) {
            $filterDateFrom = $paramData['from_date'] != '' ? $paramData['from_date'] : '';
        }
        if (isset($paramData['to_date'])) {
            $filterDateTo = $paramData['to_date'] != '' ? $paramData['to_date'] : '';
        }
        if (isset($paramData['price_from'])) {
            $filterPriceFrom = $paramData['price_from'] != '' ? $paramData['price_from'] : '';
        }
        if (isset($paramData['price_to'])) {
            $filterPriceTo = $paramData['price_to'] != '' ? $paramData['price_to'] : '';
        }
        $sellerId = $this->getCustomerId();
        $rewardCartRuleColl = $this->rewardcartFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('seller_id', ['eq'=>$sellerId]);
        if ($filterType != '') {
            $rewardCartRuleColl->addFieldToFilter('status', ['eq'=>$filterType]);
        }

        if ($filterPriceFrom) {
            $rewardCartRuleColl->addFieldToFilter('amount_from', ['gteq',$filterPriceFrom]);
        }
        if ($filterPriceTo) {
            $rewardCartRuleColl->addFieldToFilter('amount_to', ['lteq',$filterPriceTo]);
        }
        if ($filterDateFrom) {
            $rewardCartRuleColl->addFieldToFilter('start_date', ['gteq',$filterDateFrom]);
        }
        if ($filterDateTo) {
            $rewardCartRuleColl->addFieldToFilter('end_date', ['lteq',$filterDateTo]);
        }
    
        return $rewardCartRuleColl;
    }
    /**
     * @return partner id
     */
    public function getCustomerId()
    {
        return $this->mpRewardHelper->getPartnerId();
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
        return $this->marketplaceHelper;
    }
    /**
     * @return reward helper
     */
    public function getRewardHelper()
    {
        return $this->mpRewardHelper;
    }
    /**
     * @return princing helper
     */
    public function getPricingHelper()
    {
        return $this->pricingHelper;
    }
}
