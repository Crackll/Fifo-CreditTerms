<?php
/**
 * Mpshipping Controller
 *
 * @category  Webkul
 * @package   Webkul_MpPriceList
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPriceList\Controller\SellerPriceList;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Webkul\MpPriceList\Model\PriceListFactory;
use Webkul\MpPriceList\Model\AssignedRuleFactory;
use Webkul\MpPriceList\Model\DetailsFactory;
use Webkul\MpPriceList\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;

class SavePriceList extends Action
{
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
     * save price list.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $date = date("Y-m-d H:i:s");
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->isPost()) {
            try {
                $data = $this->getRequest()->getParams();
                $message = $this->validatePriceListForm($data);
                if (!empty($message)) {
                    $this->messageManager->addError($message);
                    if (array_key_exists("id", $data['pricelist'])) {
                        $id = $data['pricelist']['id'];
                        return $this->resultRedirectFactory->create()->setPath('*/*/edit/id/'.$id);
                    }
                    return $this->resultRedirectFactory->create()->setPath('*/*/addpricelist');
                }
                $priceList = $this->_priceList->create();
                if (array_key_exists("id", $data['pricelist'])) {
                    $id = $data['pricelist']['id'];
                    $priceList->addData($data['pricelist'])->setId($id)->save();
                } else {
                    $data['pricelist']['date'] = $date;
                    $priceList->setData($data['pricelist'])->save();
                }
                $priceListId = $priceList->getId();
                $pricelistRules = $this->formatPriceListData(trim($this->getRequest()->getParam("pricelist_rules")));
                $pricelistCustomers = $this->formatPriceListData(
                    trim($this->getRequest()->getParam("pricelist_customers"))
                );
              
                if (array_key_exists("pricelist_rules", $data)) {
                    $this->removeOldAssignedRules($priceListId);
                    $this->assignRules($pricelistRules, $priceListId);
                }

                if (array_key_exists("pricelist_customers", $data)) {
                    $this->removeOldAssignedCustomers($priceListId);
                    $this->assignCustomers($pricelistCustomers, $priceListId);
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
        return $resultRedirect->setPath($this->_redirect->getRefererUrl());
    }

    /**
     * validate pricelist form fields
     *
     * @param array $data
     * @return void
     */
    private function validatePriceListForm($data)
    {
        try {
            $message = '';
            $formFields = ['title','priority','start_date','end_date','status'];
            if (!empty($data)) {
                foreach ($data['pricelist'] as $fieldName => $fieldValue) {
                    if (in_array($fieldName, $formFields) && empty($fieldValue)) {
                        $message = 'Please enter the required fields values';
                        if (array_key_exists("id", $data['pricelist'])) {
                            return $message;
                        }
                    }
                }
                return $message;
            }
        } catch (\Exception $e) {
            return $message;
        }
    }
    
    /**
     * format pricelist data
     *
     * @param array $data
     * @return void
     */
    protected function formatPriceListData($data)
    {
        $formattedString = "";
        $values = explode(',', $data);
        if (!empty($values)) {
            foreach ($values as $index => $valueAtIndex) {
                if (empty($index)) {
                    $formattedString = $valueAtIndex. "=true";
                } else {
                      $formattedString.= '&'.$valueAtIndex."=true";
                }
            }
        }
        return $formattedString;
    }

    /**
     * delete item
     *
     * @param array $item
     * @return void
     */
    protected function deleteItem($item)
    {
        $item->delete();
    }

    /**
     * save data to model
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
     * remove old rules from the database
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
     * assign rules on pricelist
     *
     * @param array $pricelistRules
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
     * remove old customers
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
     * assign customers
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
                if (!empty($pricelistCustomer)) {
                    $userData = ["pricelist_id" => $priceListId, "user_id" => $pricelistCustomer, "type" => 1];
                    $this->saveItem($this->_details->create(), $userData);
                }
            }
        }
    }

    /**
     * remove old assigned groups
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
     * return formatted data
     *
     * @param string $string
     * @return void
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
