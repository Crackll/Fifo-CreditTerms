<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPriceList
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPriceList\Block\PriceList;

class AddPriceList extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\MpPriceList\Model\AssignedRuleFactory
     */
    protected $assignedRuleFactory;

    /**
     * @var \Webkul\MpPriceList\Model\PriceListFactory
     */
    protected $priceList;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @var \Webkul\MpPriceList\Helper\Data
     */
    protected $priceListHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\MpPriceList\Model\AssignedRuleFactory $assignedRuleFactory
     * @param \Webkul\MpPriceList\Model\PriceListFactory $priceList
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\MpPriceList\Helper\Data $priceListHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpPriceList\Model\AssignedRuleFactory $assignedRuleFactory,
        \Webkul\MpPriceList\Model\PriceListFactory $priceList,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MpPriceList\Helper\Data $priceListHelper
    ) {
    
        $this->assignedRuleFactory = $assignedRuleFactory;
        $this->priceList = $priceList;
        $this->marketplaceHelper = $mpHelper;
        $this->priceListHelper = $priceListHelper;
        parent::__construct($context);
    }

    /**
     * get assigned rules on pricelist
     *
     * @param int $priceListId
     * @param int $ruleId
     * @return array
     */
    public function getAssignedRuleOnPriceList($priceListId, $ruleId = null, $flag = 1)
    {
        if ($ruleId == null && $flag == 1) {
            return $this->priceListHelper->getAssignedRuleOnPriceList($priceListId);
        }
        $assignedRule = [];
        try {
            $assignedRule = $this->assignedRuleFactory->create()->getCollection()
            ->addFieldToFilter('pricelist_id', ['eq'=>$priceListId])
            ->addFieldToFilter('rule_id', ['eq'=>$ruleId]);
            if (!empty($assignedRule)) {
                return $assignedRule;
            }
            return $assignedRule;
        } catch (\Exception $e) {
            return $assignedRule;
        }
    }

    /**
     * assigned rule collection
     *
     * @param array $assignedRuleCollection
     * @return string
     */
    public function getFormattedAssignedRule($assignedRuleCollection)
    {
        $oldRulesString = "";
        try {
            if (!empty($assignedRuleCollection)) {
                foreach ($assignedRuleCollection as $assignedRuleCollectionIndex => $assignedRuleCollectionValue) {
                    if ($assignedRuleCollectionIndex == 0) {
                        $oldRulesString = $assignedRuleCollectionValue->getEntityId()."=true";
                    } else {
                        $oldRulesString.= '&'.$assignedRuleCollectionValue->getEntityId().'=true';
                    }
                }
            }
            return $oldRulesString;
        } catch (\Exception $e) {
            return $oldRulesString;
        }
    }

    /**
     * Return Customer id.
     *
     * @return bool|0|1
     */
    public function getCustomerId()
    {
        return $this->marketplaceHelper->getCustomerId();
    }

    /**
     * return status array
     *
     * @return array
     */
    public function getStatusOptions()
    {
        return $this->priceListHelper->getStatusOptions();
    }

    /**
     * validate pricelist id
     *
     * @param int $priceListId
     * @return array
     */
    public function validatePriceListId($priceListId)
    {
        return $this->priceListHelper->validatePriceListId($priceListId);
    }

    /**
     * get assigned customer on priceList
     *
     * @param int $priceListId
     * @return array
     */
    public function getAssignedCustomerOnPriceList($priceListId)
    {
        return $this->priceListHelper->getAssignedCustomerOnPriceList($priceListId);
    }

    /**
     * get formatted string
     *
     * @param array $getIds
     * @return string
     */
    public function getFormattedString($getIds)
    {
        return $this->priceListHelper->getFormattedString($getIds);
    }
}
