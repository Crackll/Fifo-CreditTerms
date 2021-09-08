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
namespace Webkul\MpPriceList\Plugin\CurrencySymbol\Controller\Adminhtml\System\Currency;

use Magento\Catalog\Model\Product as CatalogProduct;

class SaveRates
{
    /**
     * @var \Webkul\MpPriceList\Helper\Data
     */
    private $_priceListHelper;

   /**
    * @var \Webkul\MpPriceList\Model\RuleFactory
    */
    protected $rule;

    /**
     * @var  \Magento\Framework\Locale\FormatInterface
     */
    protected $formatInterface;

    /**
     * @param \Webkul\MpPriceList\Helper\Data $priceListHelper
     * @param \Magento\Framework\Locale\FormatInterface $formatInterface
     * @param \Webkul\MpPriceList\Model\RuleFactory $rule
     */
    public function __construct(
        \Webkul\MpPriceList\Helper\Data $priceListHelper,
        \Magento\Framework\Locale\FormatInterface $formatInterface,
        \Webkul\MpPriceList\Model\RuleFactory $rule
    ) {
        $this->_priceListHelper = $priceListHelper;
        $this->formatInterface = $formatInterface;
        $this->rule = $rule;
    }

    /**
     * get price
     *
     * @param CatalogProduct $subject
     * @param array $result
     * @return void
     */
    public function aroundExecute(
        \Magento\CurrencySymbol\Controller\Adminhtml\System\Currency\SaveRates $subject,
        callable $proceed
    ) {
            $data = $subject->getRequest()->getParam('rate');
        if (is_array($data)) {
            try {
                foreach ($data as $currencyCode => $rate) {
                    foreach ($rate as $currencyTo => $value) {
                        $value = abs($this->formatInterface->getNumber($value));
                        $data[$currencyCode][$currencyTo] = $value;
                    }
                }
                $this->updateCurrenciesRates($data);
            } catch (\Exception $e) {
                $subject->messageManager->addError($e->getMessage());
            }
        }
        return $proceed();
    }

   /**
    * update currency rates
    *
    * @return void
    */
    private function updateCurrenciesRates($data)
    {
        if ($this->_priceListHelper->isModuleEnabled()) {
            $baseCurrencyCode = $this->_priceListHelper->getBaseCurrencyCode();
            $priceListRules = $this->rule->create()->getCollection()->addFieldToFilter(
                'store_currency_code',
                ['in'=>array_keys($data[$baseCurrencyCode])]
            );
            if (!empty($priceListRules)) {
                foreach ($priceListRules as $rules) {
                    $oldCurrencyRates = $rules->getCurrencyRates();
                    $storeCurrencyTotal = $rules->getStoreCurrencyTotal();
                    $pricelistAmount = $rules->getStoreCurrencyAmount();
                    $newRates = $data[$baseCurrencyCode][$rules->getStoreCurrencyCode()];
                    if ($newRates != $oldCurrencyRates) {
                        if ($storeCurrencyTotal > 1) {
                            $newTotalAmount = $storeCurrencyTotal/$newRates;
                            $this->updateTotalAmount($rules, $newTotalAmount);
                        }
                            $newBaseAmount = $pricelistAmount/$newRates;
                            $this->updateRatesInRuleTable($rules, $newRates, $newBaseAmount);
                    }
                }
            }
        }
    }
    /**
     * update rates in table
     *
     * @param array $rules
     * @param double $newRates
     * @param double $newBaseAmount
     * @return void
     */
    private function updateRatesInRuleTable($rules, $newRates, $newBaseAmount)
    {
        $rules->setCurrencyRates($newRates)->save();
        if ($rules->getPriceType() == 1) {
            $rules->setAmount($newBaseAmount)->save();
        }
    }

    /**
     * update total amount
     *
     * @param array $rules
     * @param double $newTotalAmount
     * @return void
     */
    private function updateTotalAmount($rules, $newTotalAmount)
    {
        $rules->setTotal($newTotalAmount)->save();
    }
}
