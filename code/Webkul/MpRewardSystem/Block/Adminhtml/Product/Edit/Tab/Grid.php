<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpRewardSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MpRewardSystem\Block\Adminhtml\Product\Edit\Tab;

use Magento\Catalog\Model\ProductFactory;
use Webkul\MpRewardSystem\Model\ResourceModel\Rewardproduct\CollectionFactory as RewardProductCollection;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as MpProductCollection;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalog;

    protected $eavAttribute;
    protected $rewardProductCollection;
    protected $mpProductCollection;
    protected $callbackOption;

     /**
      * @param \Magento\Backend\Block\Template\Context   $context
      * @param \Magento\Backend\Helper\Data              $backendHelper
      * @param ProductFactory                            $productFactory
      * @param array                                     $data
      */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        ProductFactory $productFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        RewardProductCollection $rewardProductCollection,
        \Magento\Framework\Validator\Constraint\Option\CallbackFactory $callbackOption,
        MpProductCollection $mpProductCollection,
        array $data = []
    ) {
        $this->catalog = $productFactory;
        $this->_resource = $resource;
        $this->eavAttribute = $eavAttribute;
        $this->rewardProductCollection = $rewardProductCollection;
        $this->callbackOption = $callbackOption;
        $this->mpProductCollection = $mpProductCollection;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('rewardproductgrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }
    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'mprewardsystem/product/grid',
            ['_current' => true]
        );
    }
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $mpProductIds = $this->mpProductCollection->create()->getAllIds();
        $productIds = implode(',', $mpProductIds);
        $collection = $this->catalog->create()->getCollection();

        $proAttrId = $this->eavAttribute->getIdByCode("catalog_product", "name");

        $collection->getSelect()->joinLeft(
            ['cpev'=>$collection->getTable('catalog_product_entity_varchar')],
            'e.entity_id = cpev.entity_id',
            ['product_name'=>'value']
        )->where("cpev.store_id = 0 AND cpev.attribute_id = ".$proAttrId);

        $collection->getSelect()->joinLeft(
            ['rp'=>$collection->getTable('wk_mp_reward_products')],
            'e.entity_id = rp.product_id',
            ['points'=>'points',"status"=>'status']
        );
        if (!empty($productIds)) {
            $collection->getSelect()->where(
                'e.entity_id NOT IN ('.$productIds.')'
            );
        }
        $collection->addFilterToMap("product_name", "cpev.value");
        $collection->addFilterToMap("points", "rp.points");
        $collection->addFilterToMap("status", "rp.status");

        $this->setCollection($collection);
        parent::_prepareCollection();
    }
    protected function _setCollectionOrder($column)
    {
        if ($column->getOrderCallback()) {
            $this->$callbackOption->create(
                \Magento\Framework\Validator\Constraint\Option\Callback::class,
                [$column->getOrderCallback(), $this->getCollection(), $column]
            )->getValue();
            return $this;
        }

        return parent::_setCollectionOrder($column);
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_product',
            [
                'type' => 'checkbox',
                'name' => 'in_product',
                'index' => 'entity_id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction'
            ]
        );
        $this->addColumn(
            'entity_id',
            [
                'header' => __('Product ID'),
                'index' =>  'entity_id',
                'class' =>  'productids'
            ]
        );
        $this->addColumn(
            'product_name',
            [
                'header'                    => __('Product Name'),
                'index'                     => 'product_name',
                'filter_index'              => '`cpev`.`value`',
                'gmtoffset'                 => true,
                'filter_condition_callback' => [$this, 'filterProductName']
            ]
        );
        $this->addColumn(
            'points',
            [
                'header' => __('Reward Points'),
                'index' => 'points',
                'filter_index'              => '`rp`.`points`',
                'gmtoffset'                 => true,
                'filter_condition_callback' => [$this, 'filterPoints']
            ]
        );
        $this->addColumn(
            "status",
            [
                "header"    => __("Status"),
                "index"     => "status",
                'type' => 'options',
                'options' => $this->_getBasedOnOptions(),
                'filter_index'              => '`rp`.`status`',
                'gmtoffset'                 => true,
                'filter_condition_callback' => [$this, 'filterStatus']
            ]
        );
        return parent::_prepareColumns();
    }

    protected function _getBasedOnOptions()
    {
        return [
            0=>__('Disabled'),
            1=>__('Enabled')
        ];
    }

    public function filterProductName($collection, $column)
    {
        if (!$column->getFilter()->getCondition()) {
            return;
        }

        $condition = $collection->getConnection()
            ->prepareSqlCondition('cpev.value', $column->getFilter()->getCondition());
        $collection->getSelect()->where($condition);
    }

    public function sortProductName($collection, $column)
    {
        $collection->getSelect()->order($column->getIndex() . ' ' . strtoupper($column->getDir()));
    }
    public function filterStatus($collection, $column)
    {
        if (!$column->getFilter()->getCondition()) {
            return;
        }
        $condition = $collection->getConnection()
            ->prepareSqlCondition('rp.status', $column->getFilter()->getCondition());
        $collection->getSelect()->where($condition);
    }

    public function sortStatus($collection, $column)
    {
        $collection->getSelect()->order($column->getIndex() . ' ' . strtoupper($column->getDir()));
    }
    public function filterPoints($collection, $column)
    {
        if (!$column->getFilter()->getCondition()) {
            return;
        }
        $condition = $collection->getConnection()
            ->prepareSqlCondition('rp.points', $column->getFilter()->getCondition());
        $collection->getSelect()->where($condition);
    }

    public function sortPoints($collection, $column)
    {
        $collection->getSelect()->order($column->getIndex() . ' ' . strtoupper($column->getDir()));
    }
}
