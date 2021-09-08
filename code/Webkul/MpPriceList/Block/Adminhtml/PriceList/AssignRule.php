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

class AssignRule extends \Magento\Backend\Block\Template
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'pricelist/assign_rule.phtml';

    /**
     * @var \Magento\Catalog\Block\Adminhtml\Category\Tab\Product
     */
    protected $blockGrid;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * AssignProducts constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param AssignedRuleCollection $assignedRuleCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        AssignedRuleCollection $assignedRuleCollection,
        array $data = []
    ) {
        $this->_registry = $registry;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_assignedRuleCollection = $assignedRuleCollection;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve instance of grid block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                \Webkul\MpPriceList\Block\Adminhtml\PriceList\Rule::class,
                'mppricelist.rule.grid'
            );
        }
        return $this->blockGrid;
    }

    /**
     * Return HTML of grid block
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * @return string
     */
    public function getRulesJson()
    {
        return $this->getSelectedRulesJson();
    }
    /**
     * Retrieve current category instance
     *
     * @return array|null
     */
    public function getPriceList()
    {
        return $this->_registry->registry('mppricelist_pricelist');
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
     * get selected rules json
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
     * get selected rules
     *
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
