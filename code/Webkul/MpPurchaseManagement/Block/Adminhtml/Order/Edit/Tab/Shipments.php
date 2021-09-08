<?php
/**
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPurchaseManagement\Block\Adminhtml\Order\Edit\Tab;

class Shipments extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Webkul\MpPurchaseManagement\Model\orderItemFactory
     */
    protected $orderItemFactory;

    /**
     * @param \Webkul\MpPurchaseManagement\Model\orderItemFactory  $orderItemFactory
     * @param \Magento\Backend\Block\Template\Context              $context
     * @param \Magento\Backend\Helper\Data                         $backendHelper
     * @param array                                                $data
     */
    public function __construct(
        \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->orderItemFactory = $orderItemFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('orderShipment_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $params = $this->getRequest()->getParams();
        $collection = $this->orderItemFactory->create()->getCollection()
            ->addFieldToFilter(
                'purchase_order_id',
                [
                    'eq' => $params['id']
                ]
            )
            ->setOrder(
                'created_at',
                'DESC'
            );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id',
            [
                'header'    => __('Item'),
                'sortable'  => false,
                'index'     => 'product_id',
                'filter'    =>  false,
                'renderer'  => \Webkul\MpPurchaseManagement\Block\Adminhtml\Order\Grid\RendererProductName::class
            ]
        );
        $this->addColumn(
            'seller_id',
            [
                'header'    => __('Seller'),
                'sortable'  => false,
                'index'     => 'seller_id',
                'filter'    =>  false,
                'renderer'  => \Webkul\MpPurchaseManagement\Block\Adminhtml\Order\Grid\RendererSellerName::class
            ]
        );
        $this->addColumn(
            'ship_status',
            [
                'header'    => __('Shipping Status'),
                'sortable'  => false,
                'index'     => 'ship_status',
                'filter'    =>  false,
                'renderer'  => \Webkul\MpPurchaseManagement\Block\Adminhtml\Order\Grid\RendererStatusLabel::class
            ]
        );
        $this->addColumn(
            'schedule_date',
            [
                'header'    => __('Schedule Date'),
                'sortable'  => false,
                'index'     => 'schedule_date',
                'type'      => 'datetime'
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getRowUrl($row)
    {
        return 'javascript:void(0)';
    }
}
