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

class ProductAuctionListDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    const STOP_AUCTION_TIME = 'stop_auction_time';
    const START_AUCTION_TIME = 'start_auction_time';
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
        \Magento\Framework\App\Request\Http $request,
        array $meta = [],
        array $data = []
    ) {
        $this->request = $request;
        $getParamFilters  = $this->getParamFilters();
       
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $auctionProArray = [0];
        $productCollection = $productCollection->create()
                ->addFieldToFilter('type_id', ['nin'=> ['grouped', 'configurable','rental']])
                ->addFieldToFilter('visibility', ['neq'=>1]);
        
        $proArray = [0];
        foreach ($productCollection as $val) {
            array_push($proArray, $val->getEntityId());
        }
       
        $proInAuction = $proArray;
      
        $collectionData =  $auctionProduct->create()->getCollection()
                ->addFieldToFilter('product_id', ['in' => $proInAuction])
                ->setOrder('entity_id', 'AESC');
     
        if (isset($getParamFilters['start_auction_time'])) {
            $startAuctionFromTime = isset($getParamFilters['start_auction_time']['from'])
            ?$this->createFormatDateTime(
                $getParamFilters['start_auction_time']['from']
            )." 00:00:00":date("m-d-Y")." 00:00:00";

            $startAuctionToTime = isset($getParamFilters['start_auction_time']['to'])
            ?$this->createFormatDateTime(
                $getParamFilters['start_auction_time']['to']
            )." 23:59:59":date('m-d-Y')." 23:59:59";
            $collectionData->addFieldToFilter(
                'start_auction_time',
                ['from' => $startAuctionFromTime, 'to' => $startAuctionToTime]
            );
        }
       
        if (isset($getParamFilters['stop_auction_time'])) {
         
            $stopAuctionFromTime = isset($getParamFilters['stop_auction_time']['from'])
            ?$getParamFilters['stop_auction_time']['from']:date('m-d-Y');
            
            $stopAuctionToTime = isset($getParamFilters['stop_auction_time']['to'])
            ?$getParamFilters['stop_auction_time']['to']:date('m-d-Y');
            $stopAuctionFromTime = $stopAuctionFromTime." 00:00:00";
            $stopAuctionToTime = $stopAuctionToTime." 23:59:59";

            $collectionData->addFieldToFilter(
                'stop_auction_time',
                ['from' => $stopAuctionFromTime, 'to' => $stopAuctionToTime]
            );
        }
       
        $this->collection = $collectionData;
    }
    
    public function getParamFilters()
    {
        return $this->request->getParam('filters');
    }
     /**
      * @inheritdoc
      */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        $getParamFilters  = $this->getParamFilters();
        if ($filter->getField() == self::STOP_AUCTION_TIME || $filter->getField() == self::START_AUCTION_TIME) {
            $this->getCollection();
        } else {
            parent::addFilter($filter);
        }
    }
    public function createFormatDateTime($date)
    {
        $fomattedDate = date_create($date);
        return date_format($fomattedDate, "m/d/Y");
    }
}
