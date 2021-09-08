<?php
 /**
  * Webkul_MpAuction add Deal layout page.
  * @category  Webkul
  * @package   Webkul_MpAuction
  * @author    Webkul
  * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
  * @license   https://store.webkul.com/license.html
  */
namespace Webkul\MpAuction\Ui\DataProvider;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Webkul\MpAuction\Model\ProductFactory as AuctionProduct;
use Webkul\MpAuction\Helper\Data as AuctionHelperData;

class AccountIndexDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
{
    /**
     * Product collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $collection;

   /**
    * @var \Magento\Framework\Registry
    */
    protected $_registry;

   /**
    * Construct
    *
    * @param string $name
    * @param string $primaryFieldName
    * @param string $requestFieldName
    * @param ProductCollection $productCollection
    * @param CollectionFactory $collectionFactory
    * @param HelperData $helperData
    * @param \Magento\Framework\Registry $registry
    * @param array $meta
    * @param array $data
    */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ProductCollection $productCollection,
        AuctionHelperData $auctionHelperData,
        AuctionProduct $auctionProduct,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $productCollection,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );
        $auctionHelperData  = $auctionHelperData->getAllMpProducts();
        $mpProArray = [0];
        if (!empty($auctionHelperData)) {
            foreach ($auctionHelperData as $mpProduct) {
                array_push($mpProArray, $mpProduct->getMageproductId());
            }
        }
        $auctionProArray = [0];
        $auctionProList = $auctionProduct->create()->getCollection()
                       ->addFieldToFilter('auction_status', ['in' => [0,1]]);
        foreach ($auctionProList as $auctionPro) {
            if ($auctionPro->getProductId()) {
                $auctionProArray[] = $auctionPro->getProductId();
            }
        }
        $proInAuction = $auctionProArray;

        $collectionData = $productCollection->create()->addAttributeToSelect('*')
               ->addFieldToFilter('entity_id', ['in'=>$mpProArray])
               ->addFieldToFilter('entity_id', ['nin'=>$proInAuction])
               ->addFieldToFilter('type_id', ['nin'=> ['grouped', 'configurable', 'rental']])
               ->addFieldToFilter('visibility', ['neq'=>1])
               ->setOrder('entity_id', 'desc');
        $this->collection = $collectionData;
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }
}
