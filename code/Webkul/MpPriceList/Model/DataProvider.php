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
namespace Webkul\MpPriceList\Model;
 
use Webkul\MpPriceList\Model\ResourceModel\Rule\CollectionFactory;
use Webkul\MpPriceList\Model\ResourceModel\Items\CollectionFactory as ItemsCollection;
 
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    protected $_loadedData;
 
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $ruleCollectionFactory,
        ItemsCollection $itemsCollection,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $ruleCollectionFactory->create();
        $this->_itemsCollection = $itemsCollection;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
 
    public function getData()
    {
        if (isset($this->_loadedData)) {
            return $this->_loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $rule) {
            $this->_loadedData[$rule->getId()]['rule_details'] = $rule->getData();
            $categories = [];
            $collection = $this->_itemsCollection
                                ->create()
                                ->addFieldToFilter("entity_type", 2)
                                ->addFieldToFilter("parent_id", $rule->getId());
            foreach ($collection as $category) {
                $categories[] = $category->getEntityValue();
            }
            $categories = implode(",", $categories);
            $this->_loadedData[$rule->getId()]['rule_category']['categories'] = $categories;
        }
        return $this->_loadedData;
    }
}
