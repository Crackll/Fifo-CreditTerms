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

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Category;
use Magento\GoogleOptimizer\Model\Code as ModelCode;
use Webkul\Marketplace\Helper\Data as HelperData;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\DB\Helper as FrameworkDbHelper;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Serialize\SerializerInterface;

class AddPriceRules extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\MpPriceList\Model\RuleFactory
     */
    protected $priceRule;

    /**
     * @var \Webkul\MpPriceList\Model\ItemsFactory
     */
    protected $assignedItems;

    /**
     * @var \Webkul\Marketplace\Block\Product\Create
     */
    protected $marketplaceCreate;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @var \Webkul\MpPriceList\Helper\Data
     */
    protected $priceListHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

  /**
   * @param \Magento\Framework\View\Element\Template\Context $context
   * @param \Webkul\MpPriceList\Model\RuleFactory $priceRule
   * @param \Webkul\MpPriceList\Model\ItemsFactory $assignedItems
   * @param \Webkul\Marketplace\Block\Product\Create $marketplaceCreate
   * @param \Webkul\Marketplace\Helper\Data $mpHelper
   * @param \Webkul\MpPriceList\Helper\Data $priceListHelper
   * @param \Magento\Framework\Json\Helper\Data $jsonHelper
   */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpPriceList\Model\RuleFactory $priceRule,
        \Webkul\MpPriceList\Model\ItemsFactory $assignedItems,
        \Webkul\Marketplace\Block\Product\Create $marketplaceCreate,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MpPriceList\Helper\Data $priceListHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->priceRule = $priceRule;
        $this->assignedItems = $assignedItems;
        $this->marketplaceCreate = $marketplaceCreate;
        $this->marketplaceHelper = $mpHelper;
        $this->priceListHelper = $priceListHelper;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * calculationType options array
     *
     * @return array
     */
    public function getCalculationTypeArray()
    {
        $data = [
            ['value' => '1', 'label' => __('Increase Price')],
            ['value' => '2', 'label' => __('Decrease Price')],
        ];

        return $data;
    }

    /**
     * Price type options array
     *
     * @return array
     */
    public function getPriceTypeOptions()
    {
        $data = [
            ['value' => '1', 'label' => __('Fixed Price')],
            ['value' => '2', 'label' => __('Percent Price')],
        ];

        return $data;
    }

    /**
     * apply options array
     *
     * @return array
     */
    public function getApplyOptions()
    {
        $data = [
            ['value' => '1', 'label' => __('Product')],
            ['value' => '2', 'label' => __('Category')],
            ['value' => '3', 'label' => __('Product Quantity')],
            ['value' => '4', 'label' => __('Total Product Price')],
        ];

        return $data;
    }

    /**
     * status option array
     *
     * @return array
     */
    public function getStatusOptions()
    {
        $data = [
            ['value' => '1', 'label' => __('Active')],
            ['value' => '2', 'label' => __('Inactive')]
        ];
        return $data;
    }

    /**
     * get price rule from id
     *
     * @param int $editPriceRuleId
     * @return void
     */
    public function getEditPriceRuleData($editPriceRuleId)
    {
        try {
            $priceRuleCollection = [];
            if (!empty($editPriceRuleId)) {
                $priceRuleCollection =  $this->priceRule->create()->getCollection()->addFieldToFilter(
                    'id',
                    ['eq' => $editPriceRuleId]
                );
                if (!empty($priceRuleCollection->getSize())) {
                    foreach ($priceRuleCollection as $priceRule) {
                        return $priceRule;
                    }
                }
            }
        } catch (\Exception $e) {
            return $priceRuleCollection;
        }
    }

    /**
     * assigned product id on rules
     *
     * @param int $ruleId
     * @return void
     */
    public function getAssignedProductOnRules($ruleId)
    {
        try {
            $productsId  = [];
            $assignedItemsCollection = $this->assignedItems->create()->getCollection()
            ->addFieldToFilter('entity_type', ['eq'=>1])
            ->addFieldToFilter('parent_id', ['eq'=> $ruleId]);
            if (!empty($assignedItemsCollection->getSize())) {
                foreach ($assignedItemsCollection as $assignedItems) {
                    array_push($productsId, $assignedItems->getEntityValue());
                }
            }
            return $productsId;
        } catch (\Exception $e) {
            return $productsId;
        }
    }

    /**
     * return json for category tree
     *
     * @return string
     */
    public function getCategoriesTree()
    {
        return  $this->marketplaceCreate->getCategoriesTree();
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
     * return base currency of the store
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->priceListHelper->getBaseCurrencyCode();
    }

    /**
     * return currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->priceListHelper->getCurrentCurrencyCode();
    }

    /**
     * convert currency
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param double $amount
     */
    public function getwkconvertCurrency($fromCurrency, $toCurrency, $amount)
    {
        return $this->priceListHelper->getwkconvertCurrency($fromCurrency, $toCurrency, $amount);
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

    /**
     * show categories selected in category tree
     *
     * @param int $ruleId
     * @return void
     */
    public function showCategoriesSelectedInCategoryTree($ruleId)
    {
        return $this->priceListHelper-> showCategoriesSelectedInCategoryTree($ruleId);
    }

    /**
     * encodes data in json format
     *
     * @param array $data
     * @return json array
     */
    public function jsonEncode($data)
    {
        return $this->jsonHelper->jsonEncode($data);
    }
}
