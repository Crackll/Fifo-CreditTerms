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
namespace Webkul\MpPriceList\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpPriceList\Model\PriceListFactory;
use Webkul\MpPriceList\Model\RuleFactory;
use Webkul\MpPriceList\Model\AssignedRuleFactory;
use Webkul\MpPriceList\Model\ItemsFactory;
use Webkul\MpPriceList\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Webkul\MpPriceList\Model\PriceListFactory
     */
    protected $_priceList;

    /**
     * @var \Webkul\MpPriceList\Model\RuleFactory
     */
    protected $_rule;

    /**
     * @var \Webkul\MpPriceList\Model\AssignedRuleFactory
     */
    protected $_assignedRule;

    /**
     * @var \Webkul\MpPriceList\Model\ItemsFactory
     */
    protected $_items;

    public function __construct(
        Context $context,
        PriceListFactory $priceList,
        RuleFactory $rule,
        AssignedRuleFactory $assignedRule,
        ItemsFactory $items
    ) {
        $this->_priceList = $priceList;
        $this->_rule = $rule;
        $this->_assignedRule = $assignedRule;
        $this->_items = $items;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $date = date("Y-m-d H:i:s");
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->getRequest()->isPost()) {
            $this->messageManager->addError(__("Something went wrong"));
            return $resultRedirect->setPath('*/*/');
        }
        try {
            $ruleData = [];
            $data = $this->getRequest()->getParams();
            $ruleData = $data['rule_details'];
            $rule = $this->_rule->create();
            $applyOn = $data['rule_details']['apply_on'];
            if ($applyOn == 1 || $applyOn == 2) {
                $ruleType = 1;
                $ruleData['qty'] = 1;
                $ruleData['total'] = 1;
            } elseif ($applyOn == 3) {
                $ruleData['total'] = 1;
                $ruleType = 2;
            } elseif ($applyOn == 4) {
                $ruleData['qty'] = 1;
                $ruleType = 2;
            } else {
                $ruleType = 1;
            }
            $ruleData['rule_type'] = $ruleType;
            $ruleData['is_combination'] = 0;
            if (array_key_exists("id", $data['rule_details'])) {
                $id = $data['rule_details']['id'];
                $rule->addData($ruleData)->setId($id)->save();
            } else {
                $ruleData['date'] = $date;
                $rule->setData($ruleData)->save();
            }
            $ruleId = $rule->getId();
            $this->processData($ruleId, $ruleData, $applyOn);
            $this->messageManager->addSuccess(__("Rule saved successfully"));
            $params = ['id' => $ruleId, '_current' => true];
            return $resultRedirect->setPath('*/*/edit', $params);
        } catch (\Exception $e) {
            $this->messageManager->addError(__("Something went wrong"));
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Delete Item
     *
     * @param array $item
     * @return void
     */
    protected function deleteItem($item)
    {
        $item->delete();
    }

    /**
     * Save Item
     *
     * @param object $model
     * @param array $data
     * @return void
     */
    protected function saveItem($model, $data)
    {
        $model->setData($data)->save();
    }

    /**
     * Remove old rules
     *
     * @param int $ruleId
     * @param int $type
     * @return void
     */
    protected function removeOldRules($ruleId, $type)
    {
        $collection = $this->_items
                            ->create()
                            ->getCollection()
                            ->addFieldToFilter("parent_id", $ruleId);
        foreach ($collection as $item) {
            $this->deleteItem($item);
        }
    }

    /**
     * Create product rules
     *
     * @param array $ruleProducts
     * @param int $ruleId
     * @return void
     */
    protected function createProductRules($ruleProducts, $ruleId)
    {
        $ruleProducts = $this->getFormattedData($ruleProducts);
        foreach ($ruleProducts as $ruleProduct) {
            $ruleData = ["parent_id" => $ruleId, "entity_type" => 1, "entity_value" => $ruleProduct];
            $this->saveItem($this->_items->create(), $ruleData);
        }
    }

    /**
     * Create category rules
     *
     * @param array $ruleCategory
     * @param int $ruleId
     * @return void
     */
    protected function createCategoryRules($ruleCategory, $ruleId)
    {
        try {
            foreach ($ruleCategory['categories'] as $category) {
                $ruleData = ["parent_id" => $ruleId, "entity_type" => 2, "entity_value" => $category];
                $this->saveItem($this->_items->create(), $ruleData);
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Get formatted data
     *
     * @param string $string
     * @return string
     */
    protected function getFormattedData($string)
    {
        $string = str_replace("=true&", "+", $string);
        $string = str_replace("=true", "", $string);
        $string = str_replace("0=0&", "", $string);
        if (strpos($string, "+") !== false) {
            $data = explode("+", $string);
        } else {
            $data = [$string];
        }
        return $data;
    }

    /**
     * process rule data
     *
     * @param int $ruleId
     * @param array $ruleData
     * @param int $applyOn
     * @return void
     */
    protected function processData($ruleId, $ruleData, $applyOn)
    {
        $data = $this->getRequest()->getParams();
        $ruleProducts = trim($this->getRequest()->getParam("pricelist_rule_products"));
        $ruleCategory = $this->getRequest()->getParam("rule_category");
        if ($applyOn == 1) {
            if (array_key_exists("pricelist_rule_products", $data)) {
                $this->removeOldRules($ruleId, 1);
                $this->createProductRules($ruleProducts, $ruleId);
            }
        } elseif ($applyOn == 2) {
            if (array_key_exists("categories", $ruleCategory)) {
                $this->removeOldRules($ruleId, 2);
                $this->createCategoryRules($ruleCategory, $ruleId);
            }
        } elseif ($applyOn == 3) {
            $this->removeOldRules($ruleId, 3);
            $ruleData = ["parent_id" => $ruleId, "entity_type" => 3, "entity_value" => $ruleData['qty']];
            $this->saveItem($this->_items->create(), $ruleData);
        } else {
            $this->removeOldRules($ruleId, 4);
            $ruleData = ["parent_id" => $ruleId, "entity_type" => 4, "entity_value" => $ruleData['total']];
            $this->saveItem($this->_items->create(), $ruleData);
        }
    }
}
