<?php
/**
 * @category  Webkul
 * @package   Webkul_MpPriceList
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPriceList\Controller\PriceRules;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Url;
use Webkul\MpPriceList\Model\RuleFactory;
use Webkul\MpPriceList\Model\ItemsFactory;

class Save extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;
    /**
     * @var Webkul\Mpshipping\Model\MpshippingmethodFactory
     */
    protected $_mpshippingMethod;
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $_csvReader;
    /**
     * @var Magento\Customer\Model\Url
     */
    protected $_customerUrl;
    /**
     * @var Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploader;

   /**
    * @param Context $context
    * @param Session $customerSession
    * @param FormKeyValidator $formKeyValidator
    * @param PageFactory $resultPageFactory
    * @param \Magento\Framework\File\Csv $csvReader
    * @param Url $customerUrl
    * @param RuleFactory $rule
    * @param ItemsFactory $items
    * @param \Webkul\MpPriceList\Helper\Data $priceListHelper
    */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        PageFactory $resultPageFactory,
        \Magento\Framework\File\Csv $csvReader,
        Url $customerUrl,
        RuleFactory $rule,
        ItemsFactory $items,
        \Webkul\MpPriceList\Helper\Data $priceListHelper
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_csvReader = $csvReader;
        $this->_customerUrl = $customerUrl;
        $this->_rule = $rule;
        $this->_items = $items;
        $this->priceListHelper = $priceListHelper;
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_customerUrl->getLoginUrl();
        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * save Shipping rate.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $date = date("Y-m-d H:i:s");
        $resultRedirect = $this->resultRedirectFactory->create();
        $allowedCurrencies = $this->priceListHelper->getConfigAllowCurrencies();
        $baseCurrencyCode = $this->priceListHelper->getBaseCurrencyCode();
        $storeCurrencyCode =  $this->priceListHelper->getCurrentCurrencyCode();
        $rates = $this->priceListHelper->getCurrencyRates(
            $baseCurrencyCode,
            array_values($allowedCurrencies)
        );
        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->getRequest()->isPost()) {
                    $this->messageManager->addError(__("Something went wrong"));
                    return $resultRedirect->setPath('*/*/');
                }
                $ruleData = [];
                $data = $this->getRequest()->getParams();
                $message = $this->validatePriceRuleData($data);
                if (!empty($message)) {
                    $this->messageManager->addError($message);
                    if (array_key_exists("id", $data)) {
                        $id = $data['id'];
                        return $this->resultRedirectFactory->create()->setPath('*/*/edit/id/'.$id);
                    }
                    return $this->resultRedirectFactory->create()->setPath('*/*/manageruleslist');
                }
                $ruleData = $data;
                $rule = $this->_rule->create();
                $ruleData['currency_rates'] = $this->getCurrencyRatesByStore();
                $applyOn = $ruleData['apply_on'];
                if ($applyOn == 1 || $applyOn == 2) {
                    $ruleType = 1;
                    $ruleData['qty'] = 1;
                    $ruleData['total'] = 1;
                    $ruleData['store_currency_total']=1;
                } elseif ($applyOn == 3) {
                    $ruleData['total'] = 1;
                    $ruleData['store_currency_total']=1;
                    $ruleType = 2;
                } elseif ($applyOn == 4) {
                    $ruleData['qty'] = 1;
                    $ruleData['total'] = $ruleData['store_currency_total'];
                    $ruleType = 2;
                } else {
                    $ruleType = 1;
                }
                $ruleData['base_currency_code'] = $baseCurrencyCode;
                $ruleData['store_currency_code'] = $storeCurrencyCode;
                $ruleData['amount'] = $this->getRuleAmountByStore($ruleData);
                $ruleData['total'] = $this->getTotalAmountByStore($ruleData);
                $ruleData['rule_type'] = $ruleType;
                $ruleData['is_combination'] = 0;
                if (array_key_exists("id", $data)) {
                    $id = $data['id'];
                    $rule->addData($ruleData)->setId($id)->save();
                } else {
                    $ruleData['date'] = $date;
                    $rule->setData($ruleData)->save();
                }
                $ruleId = $rule->getId();
                $this->processData($ruleId, $ruleData, $applyOn);
                $this->messageManager->addSuccess(__("Rule saved successfully"));
                $this->priceListHelper->clearCache();
                $params = ['id' => $ruleId, '_current' => true];
                return $resultRedirect->setPath('*/*/edit', $params);
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath('*/*/manageruleslist');
            }
        }
        return $this->resultRedirectFactory->create()->setPath('mppricelist/sellerpricelist/addpricerules');
    }

     /**
      * get amount by store
      *
      * @param array $ruleData
      * @return void
      */
    private function getTotalAmountByStore($ruleData)
    {
        try {
            $baseCurrencyCode = $this->priceListHelper->getBaseCurrencyCode();
            $storeCurrencyCode =  $this->priceListHelper->getCurrentCurrencyCode();
            $total = $ruleData['store_currency_total'];
            if ($ruleData['store_currency_total']>1 & $baseCurrencyCode != $storeCurrencyCode) {
                $total =  $this->priceListHelper->getwkconvertCurrency(
                    $storeCurrencyCode,
                    $baseCurrencyCode,
                    $ruleData['store_currency_total']
                );
            }
        } catch (\Exception $e) {
            return $total = 1;
        }
        return $total;
    }

    /**
     * get rule amount by store
     *
     * @param array $ruleData
     * @return void
     */
    private function getRuleAmountByStore($ruleData)
    {
        try {
            $amount =0;
            $baseCurrencyCode = $this->priceListHelper->getBaseCurrencyCode();
            $storeCurrencyCode =  $this->priceListHelper->getCurrentCurrencyCode();
            $amount = $ruleData['store_currency_amount'];
            if ($baseCurrencyCode != $storeCurrencyCode) {
                if ($ruleData['price_type'] == 1) {
                    $amount =  $this->priceListHelper->getwkconvertCurrency(
                        $storeCurrencyCode,
                        $baseCurrencyCode,
                        $ruleData['store_currency_amount']
                    );
                }
            }
            return $amount;
        } catch (\Exception $e) {
            return $amount;
        }
        
        return $amount;
    }

    /**
     * get currency rates by store
     *
     * @return void
     */
    private function getCurrencyRatesByStore()
    {
        try {
            $currencyRates =0;
            $allowedCurrencies = $this->priceListHelper->getConfigAllowCurrencies();
            $baseCurrencyCode = $this->priceListHelper->getBaseCurrencyCode();
            $storeCurrencyCode =  $this->priceListHelper->getCurrentCurrencyCode();
            $rates = $this->priceListHelper->getCurrencyRates(
                $baseCurrencyCode,
                array_values($allowedCurrencies)
            );
            if (!empty($rates) && array_key_exists($storeCurrencyCode, $rates)) {
                $currencyRates = $rates[$storeCurrencyCode];
            }
        } catch (\Exception $e) {
            return $currencyRates;
        }
        return $currencyRates;
    }

    /**
     * validate form post data
     *
     * @param array $data
     * @return void
     */
    private function validatePriceRuleData($data)
    {
        $message = '';
        $requiredFields = [
            'title',
            'calculation_type',
            'price_type',
            'store_currency_amount',
            'apply_on',
            'priority',
            'status'
        ];
        if (!empty($data)) {
            foreach ($data as $fieldName => $fieldValue) {
                if (in_array($fieldName, $requiredFields) && empty($fieldValue)) {
                    $message = 'Please fill the required Fields';
                    return $message;
                }
            }
            if ($data['apply_on'] == 2 && empty($data['product'])) {
                $message = 'categories is a required field';
                return $message;
            }
            if ($data['apply_on'] == 3 && empty($data['qty'])) {
                $message = 'Total quantity is a required field';
                return $message;
            }
            if ($data['apply_on'] == 4 && empty($data['store_currency_total'])) {
                $message = 'Total product price is a required field';
                return $message;
            }
            return $message;
        }
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
     * save data
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
     * remove old rules
     *
     * @param int $ruleId
     * @param string $type
     * @return void
     */
    protected function removeOldRules($ruleId)
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
     * create product rules
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
     * create rules category
     *
     * @param  $ruleCategory
     * @param int $ruleId
     * @return boolean
     */
    protected function createCategoryRules($ruleCategory, $ruleId)
    {
        try {
            foreach ($ruleCategory['category_ids'] as $category) {
                $ruleData = ["parent_id" => $ruleId, "entity_type" => 2, "entity_value" => $category];
                $this->saveItem($this->_items->create(), $ruleData);
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * get formatted data
     *
     * @param string $string
     * @return array
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
     * process data
     *
     * @param int $ruleId
     * @param array $ruleData
     * @param string $applyOn
     * @return array
     */
    protected function processData($ruleId, $ruleData, $applyOn)
    {
        $productString ='';
        $data = $this->getRequest()->getParams();
        $productIdString = trim($this->getRequest()->getParam("pricelist_rule_products"));
        $ruleProducts = explode(',', $productIdString);
        foreach ($ruleProducts as $keyId => $productIdValue) {
            if ($keyId == 0) {
                $productString =  $productIdValue. "=true";
            } else {
                $productString.= '&'.$productIdValue.'=true';
            }
        }
        $ruleProducts = $productString;
        $ruleCategory = $this->getRequest()->getParam("product");
        if ($applyOn == 1) {
            if (array_key_exists("pricelist_rule_products", $data)) {
                $this->removeOldRules($ruleId);
                $this->createProductRules($ruleProducts, $ruleId);
            }
        } elseif ($applyOn == 2) {
            if (array_key_exists("category_ids", $ruleCategory)) {
                $this->removeOldRules($ruleId);
                $this->createCategoryRules($ruleCategory, $ruleId);
            }
        } elseif ($applyOn == 3) {
            $this->removeOldRules($ruleId);
            $ruleData = ["parent_id" => $ruleId, "entity_type" => 3, "entity_value" => $ruleData['qty']];
            $this->saveItem($this->_items->create(), $ruleData);
        } else {
            $this->removeOldRules($ruleId);
            $ruleData = ["parent_id" => $ruleId, "entity_type" => 4, "entity_value" => $ruleData['total']];
            $this->saveItem($this->_items->create(), $ruleData);
        }
    }
}
