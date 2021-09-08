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
 
use Webkul\MpPriceList\Model\ResourceModel\PriceList\CollectionFactory;
use Webkul\MpPriceList\Model\ResourceModel\Details\CollectionFactory as UserCollection;
 
class PriceListDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    protected $_loadedData;
 
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        UserCollection $userCollection,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->_userCollection = $userCollection;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
 
    public function getData()
    {
        if (isset($this->_loadedData)) {
            return $this->_loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $pricelist) {
            $data = ["pricelist" => $pricelist->getData()];
            $groups = [];
            $collection = $this->_userCollection
                            ->create()
                            ->addFieldToFilter("type", 2)
                            ->addFieldToFilter("pricelist_id", $pricelist->getId());
            foreach ($collection as $group) {
                $groups[] = $group->getUserId();
            }
            $groups = implode(",", $groups);
            $data['assign_customer_group'] = ["customer_group" => $groups];
            $this->_loadedData[$pricelist->getId()] = $data;
        }
        return $this->_loadedData;
    }
}
