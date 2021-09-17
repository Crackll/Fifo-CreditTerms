<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPromotionCampaign
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPromotionCampaign\Model\ResourceModel\CampaignProduct\Grid;

use Webkul\Marketplace\Helper\Data as HelperData;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Webkul\MpPromotionCampaign\Model\ResourceModel\CampaignProduct\Collection as CampaignProductCollection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Collection
 * Collection for displaying grid
 */
class Collection extends CampaignProductCollection implements SearchResultInterface
{
    /**
     * Resource initialization
     * @param EntityFactoryInterface   $entityFactory,
     * @param LoggerInterface          $logger,
     * @param FetchStrategyInterface   $fetchStrategy,
     * @param ManagerInterface         $eventManager,
     * @param StoreManagerInterface    $storeManagerInterface
     * @param String                   $mainTable,
     * @param String                   $eventPrefix,
     * @param String                   $eventObject,
     * @param String                   $resourceModel,
     * @param $model = 'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
     * @param $connection = null,
     * @param AbstractDb              $resource = null
     * @return $this
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Webkul\MpPromotionCampaign\Helper\Data $helper,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\RequestInterface $request,
        HelperData $helperData,
        ProductCollection $productCollection,
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManagerInterface,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->addFilterToMap(
            'entity_id',
            'main_table.entity_id'
        );
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManagerInterface,
            $connection,
            $resource
        );
        $this->productStatus = $productStatus;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->redirect = $redirect;
        $this->request = $request;
        $this->productCollection = $productCollection;
        $this->helperData = $helperData;
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     *
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null
    ) {

        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Runs before rendering Grid
     *
     * @return void
     */
    public function _renderFiltersBefore()
    {
        $sellerId = $this->helperData->getCustomerId();
        $campaignId = '';
        $campaignId =  $this->request->getParam('id');
        if (empty($campaignId)) {
            $url = $this->redirect->getRefererUrl();
            $urlData = $this->helper->getCampaignIdFromUrl($url);
            $campaignId = $urlData;
        }
        $productDecimal = $this->getTable('catalog_product_entity_decimal');
        $productInt = $this->getTable('catalog_product_entity_int');
        $productVar = $this->getTable('catalog_product_entity_varchar');
        $productEntity = $this->getTable('catalog_product_entity');
        $this->getSelect()
        ->where('seller_campaign_id = '.$sellerId)
        ->where('campaign_id = '.$campaignId)
        ->where('status != 0')
     
        ->joinLeft(
            ['product_decimal' =>$productDecimal],
            'product_decimal.entity_id = main_table.product_id',
            [
                'product_price'=>'product_decimal.value'
            ]
        )
        ->joinLeft(
            ['product_int' =>$productInt],
            'product_int.entity_id = main_table.product_id',
            [
                'product_status'=>'product_int.value'
            ]
        )
        ->joinLeft(
            ['product_varchar' =>$productVar],
            'product_varchar.entity_id = main_table.product_id',
            [
            'name'=>'product_varchar.value'
            ]
        )
        ->joinLeft(
            ['product_entity' =>$productEntity],
            'product_entity.entity_id = main_table.product_id',
            [
                'sku'=>'sku'
            ]
        )->group('main_table.product_id');
        parent::_renderFiltersBefore();
    }
}
