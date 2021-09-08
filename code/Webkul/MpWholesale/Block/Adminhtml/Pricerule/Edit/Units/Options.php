<?php
namespace Webkul\MpWholesale\Block\Adminhtml\Pricerule\Edit\Units;

class Options extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $adminSession;

    /**
     * @var string
     */
    protected $_template = 'Webkul_MpWholesale::pricerule/pricerule.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\Auth\Session $adminSession
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Webkul\MpWholesale\Model\UnitMappingFactory $unitMapping,
        \Webkul\MpWholesale\Model\WholeSalerUnitFactory $wholeSalerUnits,
        \Magento\Backend\Model\Auth\Session $adminSession,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->unitMapping = $unitMapping;
        $this->wholeSalerUnits = $wholeSalerUnits;
        $this->adminSession = $adminSession;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve units option values
     *
     * @return array
     */
    public function getOptionValues()
    {
        $values = $this->_getData('option_values');
        if ($values === null) {
            $values = [];

            $priceRule = $this->getPriceRuleObject();
            $optionCollection = $this->_getOptionValuesCollection($priceRule);
            if ($optionCollection) {
                $values = $this->_prepareOptionValues($priceRule, $optionCollection);
            }

            $this->setData('option_values', $values);
        }

        return $values;
    }

    /**
     * Retrieve Units collection
     *
     * @return array
     */
    public function getUnitsData()
    {
        if (!$this->hasUnits()) {
            $userId = $this->adminSession->getUser()->getUserId();
            $unitsCollection = $this->wholeSalerUnits->create()
                                    ->getCollection()
                                    ->addFieldToFilter('user_id', $userId);
            $units  =   $unitsCollection->getData();
            $this->setData('units', $units);
        }
        return $this->_getData('units');
    }

    /**
     * Returns units sorted by Sort Order
     *
     * @return array
     * @since 100.1.0
     */
    public function getUnitsSortedBySortOrder()
    {
        $units = $this->getUnitsData();
        if (is_array($units)) {
            usort($units, function ($unitA, $unitB) {
                if ($unitA['sort_order'] == $unitB['sort_order']) {
                    return $unitA['entity_id'] < $unitB['entity_id'] ? -1 : 1;
                }
                return ($unitA['sort_order'] < $unitB['sort_order']) ? -1 : 1;
            });
        }
        return $units;
    }

    public function getUnitsDataWithValue()
    {
        $units = $this->getUnitsSortedBySortOrder();
        $data = [];
        foreach ($units as $unit) {
            $data[$unit['entity_id']] = $unit['unit_name'];
        }
        return $data;
    }

    /**
     * Retrieve option values collection
     *
     * @param \Webkul\MpWholesale\Model\PriceRule $priceRule
     * @return array|\Webkul\MpWholesale\Model\UnitMapping
     */
    protected function _getOptionValuesCollection(\Webkul\MpWholesale\Model\PriceRule $priceRule)
    {
        return $this->unitMapping->create()->getCollection()->addFieldToFilter(
            'rule_id',
            ['eq' => $priceRule->getEntityId()]
        )->load();
    }

    /**
     * @param \Webkul\MpWholesale\Model\PriceRule $priceRule
     * @param array|\Webkul\MpWholesale\Model\UnitMapping $optionCollection
     * @return array
     */
    protected function _prepareOptionValues(
        \Webkul\MpWholesale\Model\PriceRule $priceRule,
        $optionCollection
    ) {
        $inputType = 'select';
        $values = [];
        foreach ($optionCollection as $option) {
            $bunch = $this->_preparePriceRuleOptionValues(
                $option,
                $inputType
            );
            foreach ($bunch as $value) {
                $values[] = new \Magento\Framework\DataObject($value);
            }
        }

        return $values;
    }

    /**
     * Prepare option values of user defined attribute
     *
     * @param array|\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option $option
     * @param string $inputType
     * @param array $defaultValues
     * @return array
     */
    protected function _preparePriceRuleOptionValues($option, $inputType)
    {
        $unitId = $option->getUnitId();
        $optionId = $option->getEntityId();

        $value['unit_id'] = $unitId;
        $value['intype'] = $inputType;
        $value['id'] = 'option_'.$optionId;
        $value['entity_id'] = $optionId;
        $value['sort_order'] = $option->getSortOrder();
        $value['qty'] = $option->getQty();
        $value['qty_price'] = $option->getQtyPrice();
        $value['units_Data'] = $this->getUnitsDataWithValue();

        return [$value];
    }

    /**
     * Retrieve Pricerule object from registry
     *
     * @return \Webkul\MpWholesale\Model\PriceRule
     */
    public function getPriceRuleObject()
    {
        return $this->registry->registry('entity_pricerule');
    }
}
