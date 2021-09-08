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
namespace Webkul\MpPriceList\Controller\Adminhtml\PriceList;

use Magento\Backend\App\Action\Context;
use Webkul\MpPriceList\Model\PriceListFactory;
use Webkul\MpPriceList\Model\AssignedRuleFactory;
use Webkul\MpPriceList\Model\DetailsFactory;
use Webkul\MpPriceList\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Webkul\MpPriceList\Model\PriceListFactory
     */
    protected $_priceList;

    /**
     * @var \Webkul\MpPriceList\Model\AssignedRuleFactory
     */
    protected $_assignedRule;

    /**
     * @var \Webkul\MpPriceList\Model\DetailsFactory
     */
    protected $_details;

    /**
     * @param Context $context
     * @param PriceListFactory $priceList
     * @param AssignedRuleFactory $assignedRule
     * @param DetailsFactory $details
     */
    public function __construct(
        Context $context,
        PriceListFactory $priceList,
        AssignedRuleFactory $assignedRule,
        DetailsFactory $details
    ) {
        $this->_priceList = $priceList;
        $this->_assignedRule = $assignedRule;
        $this->_details = $details;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $date = date("Y-m-d H:i:s");
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->isPost()) {
            try {
                $data = $this->getRequest()->getParams();
                $priceList = $this->_priceList->create();
                if (strtotime($data['pricelist']['start_date']) >= strtotime($data['pricelist']['end_date'])) {
                    $this->messageManager->addError(__("Start date should be less than End date"));
                    if (array_key_exists("id", $data['pricelist'])) {
                        $params = ['id' => $data['pricelist']['id'], '_current' => true];
                        return $resultRedirect->setPath('mppricelist/pricelist/edit', $params);
                    } else {
                        return $resultRedirect->setPath('mppricelist/pricelist/new');
                    }
                }
                if (array_key_exists("id", $data['pricelist'])) {
                    $id = $data['pricelist']['id'];
                    $priceList->addData($data['pricelist'])->setId($id)->save();
                } else {
                    $data['pricelist']['date'] = $date;
                    $priceList->setData($data['pricelist'])->save();
                }
                $priceListId = $priceList->getId();
                $pricelistRules = trim($this->getRequest()->getParam("pricelist_rules"));
                $pricelistCustomers = trim($this->getRequest()->getParam("pricelist_customers"));
                $customerGroup = $this->getRequest()->getParam("assign_customer_group");

                if (array_key_exists("pricelist_rules", $data)) {
                    $this->removeOldAssignedRules($priceListId);
                    $this->assignRules($pricelistRules, $priceListId);
                }

                if (array_key_exists("pricelist_customers", $data)) {
                    $this->removeOldAssignedCustomers($priceListId);
                    $this->assignCustomers($pricelistCustomers, $priceListId);
                }

                if (array_key_exists("assign_customer_group", $data)) {
                    if (array_key_exists("customer_group", $customerGroup)) {
                        $this->removeOldAssignedGroups($priceListId);
                        $this->assignGroups($customerGroup, $priceListId);
                    }
                }
                $this->messageManager->addSuccess(__("Pricelist saved successfully"));
                $params = ['id' => $priceListId, '_current' => true];
                return $resultRedirect->setPath('*/*/edit', $params);
            } catch (\Exception $e) {
                $this->messageManager->addError(__("Something went wrong"));
            }
        } else {
            $this->messageManager->addError(__("Something went wrong"));
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Delete Item
     *
     * @param object $item
     * @return void
     */
    protected function deleteItem($item)
    {
        $item->delete();
    }

    /**
     * Save Item
     *
     * @param object $item
     * @return void
     */
    protected function saveItem($model, $data)
    {
        $model->setData($data)->save();
    }

    /**
     * Remove old assigned rules
     *
     * @param int $priceListId
     * @return void
     */
    protected function removeOldAssignedRules($priceListId)
    {
        $collection = $this->_assignedRule
                            ->create()
                            ->getCollection()
                            ->addFieldToFilter("pricelist_id", $priceListId);
        foreach ($collection as $item) {
            $this->deleteItem($item);
        }
    }

    /**
     * Save assign rules
     *
     * @param int $priceListId
     * @return void
     */
    protected function assignRules($pricelistRules, $priceListId)
    {
        if ($pricelistRules != "") {
            $pricelistRules = $this->getFormattedData($pricelistRules);
            foreach ($pricelistRules as $pricelistRule) {
                $ruleData = ["pricelist_id" => $priceListId, "rule_id" => $pricelistRule];
                $this->saveItem($this->_assignedRule->create(), $ruleData);
            }
        }
    }

    /**
     * Remove old assigned rules
     *
     * @param int $priceListId
     * @return void
     */
    protected function removeOldAssignedCustomers($priceListId)
    {
        $collection = $this->_details
                            ->create()
                            ->getCollection()
                            ->addFieldToFilter("type", 1)
                            ->addFieldToFilter("pricelist_id", $priceListId);
        foreach ($collection as $item) {
            $this->deleteItem($item);
        }
    }

    /**
     * Save assign customers
     *
     * @param array $pricelistCustomers
     * @param int $priceListId
     * @return void
     */
    protected function assignCustomers($pricelistCustomers, $priceListId)
    {
        if ($pricelistCustomers != "") {
            $pricelistCustomers = $this->getFormattedData($pricelistCustomers);
            foreach ($pricelistCustomers as $pricelistCustomer) {
                $userData = ["pricelist_id" => $priceListId, "user_id" => $pricelistCustomer, "type" => 1];
                $this->saveItem($this->_details->create(), $userData);
            }
        }
    }

    /**
     * Remove old assigned customers groups
     *
     * @param int $priceListId
     * @return void
     */
    protected function removeOldAssignedGroups($priceListId)
    {
        $collection = $this->_details
                            ->create()
                            ->getCollection()
                            ->addFieldToFilter("type", 2)
                            ->addFieldToFilter("pricelist_id", $priceListId);
        foreach ($collection as $item) {
            $this->deleteItem($item);
        }
    }

    /**
     * Save assign groups
     *
     * @param array $customerGroup
     * @param int $priceListId
     * @return void
     */
    protected function assignGroups($customerGroup, $priceListId)
    {
        try {
            foreach ($customerGroup['customer_group'] as $group) {
                $userData = ["pricelist_id" => $priceListId, "user_id" => $group, "type" => 2];
                $this->saveItem($this->_details->create(), $userData);
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Get Formatted Data
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
}
