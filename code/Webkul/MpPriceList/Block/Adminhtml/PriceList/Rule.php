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
namespace Webkul\MpPriceList\Block\Adminhtml\PriceList;

use Magento\Customer\Controller\RegistryConstants;
use Webkul\MpPriceList\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;
use Webkul\MpPriceList\Model\ResourceModel\AssignedRule\CollectionFactory as AssignedRuleCollection;

class Rule extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var  \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param RuleCollection $ruleCollection
     * @param AssignedRuleCollection $assignedRuleCollection
     * @param \Webkul\MpPriceList\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        RuleCollection $ruleCollection,
        AssignedRuleCollection $assignedRuleCollection,
        \Webkul\MpPriceList\Helper\Data $helper,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_coreRegistry = $coreRegistry;
        $this->_ruleCollection = $ruleCollection;
        $this->_assignedRuleCollection = $assignedRuleCollection;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('mppricelist_rule_grid');
        $this->setDefaultSort('id', 'desc');
        $this->setUseAjax(true);
    }

    /**
     * Apply various selection filters to prepare collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $sellerId = 0;
        if ($this->getPriceList()->getId()) {
            $this->setDefaultFilter(['in_pricelist' => 1]);
        }
        $collection = $this->_ruleCollection->create();
        if ($this->getPriceList()->getSellerId()) {
            $sellerId = $this->getPriceList()->getSellerId();
            $collection->addFieldToFilter('seller_id', $sellerId);
        } else {
            $collection->addFieldToFilter('seller_id', ['eq' => 0]);
        }
        $this->setCollection($collection);
        $ruleIds = $this->_getSelectedRules();
        if (empty($ruleIds)) {
            $ruleIds = 0;
        }
        return parent::_prepareCollection();
    }

    /**
     * @return array|null
     */
    public function getPriceList()
    {
        return $this->_coreRegistry->registry('mppricelist_pricelist');
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_pricelist') {
            $ruleIds = $this->_getSelectedRules();
            if (empty($ruleIds)) {
                $ruleIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('id', ['in' => $ruleIds]);
            } elseif (!empty($ruleIds)) {
                $this->getCollection()->addFieldToFilter('id', ['nin' => $ruleIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * prepare columns
     *
     * @return void
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_pricelist',
            [
                'type' => 'checkbox',
                'name' => 'in_pricelist',
                'values' => $this->_getSelectedRules(),
                'index' => 'id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction'
            ]
        );
        $this->addColumn('id', ['header' => __('Id'), 'width' => '100', 'index' => 'id']);
        $this->addColumn('title', ['header' => __('Title'), 'index' => 'title']);
        $this->addColumn(
            'calculation_type',
            [
                'type' => 'options',
                'header' => __('Calculation Type'),
                'index' => 'calculation_type',
                'options' => $this->_helper->getCalculationTypeOptions()
            ]
        );
        $this->addColumn(
            'price_type',
            [
                'type' => 'options',
                'header' => __('Price Type'),
                'index' => 'price_type',
                'options' => $this->_helper->getPriceTypeOptions()
            ]
        );
        $this->addColumn('amount', ['header' => __('Amount'), 'index' => 'amount']);
        $this->addColumn('priority', ['header' => __('Priority'), 'index' => 'priority']);
        $this->addColumn(
            'status',
            [
                'type' => 'options',
                'header' => __('Status'),
                'index' => 'status',
                'options' => $this->_helper->getAdminGridStatusOptions()
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * get grid url
     *
     * @return void
     */
    public function getGridUrl()
    {
        return $this->getUrl('mppricelist/pricelist/rule', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function _getSelectedRules()
    {
        $rules = array_keys($this->getSelectedRules());
        return $rules;
    }

    /**
     * get selected rules
     *
     * @return array
     */
    public function getSelectedRulesJson()
    {
        $jsonRules = [];
        $rules = array_keys($this->getSelectedRules());
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                $jsonRules[$rule] = 0;
            }
            return $this->_jsonEncoder->encode((object)$jsonRules);
        }
        return '{}';
    }

    /**
     * @return array
     */
    public function getSelectedRules()
    {
        $allRules = $this->getRequest()->getPost('pricelist_rules');
        $rules = [];
        if ($allRules === null) {
            $priceList = $this->getPriceList();
            $id = $priceList->getId();
            $collection = $this->_assignedRuleCollection
                                ->create()
                                ->addFieldToFilter("pricelist_id", $id);
            foreach ($collection as $rule) {
                $rules[$rule->getRuleId()] = ['position' => $rule->getRuleId()];
            }
        } else {
            foreach ($allRules as $rule) {
                $rules[$rule] = ['position' => $rule];
            }
        }
        return $rules;
    }
}
