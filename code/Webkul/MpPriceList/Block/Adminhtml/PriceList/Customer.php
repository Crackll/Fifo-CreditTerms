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
namespace Webkul\MpPriceList\Block\Adminhtml\PriceList;

use Magento\Customer\Controller\RegistryConstants;
use Webkul\MpPriceList\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;
use Webkul\MpPriceList\Model\ResourceModel\Details\CollectionFactory as DetailsCollection;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory as SaleslistCollection;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection as OrderCollection;

class Customer extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Webkul\MpPriceList\Model\ResourceModel\Details\CollectionFactory
     */
    protected $_detailsCollection;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customer;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection
     */
    protected $_orderCollection;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory
     */
    protected $_salesListCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param DetailsCollection $detailsCollection
     * @param \Magento\Customer\Model\CustomerFactory $customer
     * @param OrderCollection $orderCollection
     * @param SaleslistCollection $salesListCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        DetailsCollection $detailsCollection,
        \Magento\Customer\Model\CustomerFactory $customer,
        OrderCollection $orderCollection,
        SaleslistCollection $salesListCollectionFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_detailsCollection = $detailsCollection;
        $this->_customer = $customer;
        $this->_orderCollection = $orderCollection;
        $this->_salesListCollectionFactory = $salesListCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('admin_pricelist_customer_grid');
        $this->setDefaultSort('entity_id', 'desc');
        $this->setUseAjax(true);
    }

    /**
     * Apply various selection filters to prepare the sales order grid collection.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        if ($this->getPriceList()->getId()) {
            $this->setDefaultFilter(['in_customer' => 1]);
        }
        if ($this->getPriceList()->getSellerId()) {
            $sellerId = $this->getPriceList()->getSellerId();
            $customerGridFlat = $this->_orderCollection->getTable('customer_grid_flat');
            $collection = $this->_salesListCollectionFactory->create()
            ->addFieldToFilter('seller_id', $sellerId)
            ->addFieldToSelect('magebuyer_id');

            $collection->getSelect()
            ->columns('SUM(actual_seller_amount) AS customer_base_total')
            ->columns('count(distinct(order_id)) AS order_count')
            ->group('magebuyer_id');

            $collection->getSelect()->join(
                $customerGridFlat.' as cgf',
                'main_table.magebuyer_id = cgf.entity_id',
                [
                    'name' => 'name',
                    'email' => 'email',
                    'billing_telephone' => 'billing_telephone',
                    'gender' => 'gender',
                    'billing_full' => 'billing_full',
                    'entity_id' => 'entity_id'
                ]
            );
        } else {
            $collection = $this->_customer->create()->getCollection()->addNameToSelect();
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return array|null
     */
    public function getPriceList()
    {
        return $this->_coreRegistry->registry('mppricelist_pricelist');
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_customer') {
            $customerIds = $this->_getSelectedCustomers();
            if (empty($customerIds)) {
                $customerIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $customerIds]);
            } elseif (!empty($customerIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $customerIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * prepare columns
     *
     * @return void
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_customer',
            [
                'type' => 'checkbox',
                'name' => 'in_customer',
                'values' => $this->_getSelectedCustomers(),
                'index' => 'entity_id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction'
            ]
        );
        $this->addColumn('entity_id', ['header' => __('Id'), 'width' => '100', 'index' => 'entity_id']);
        $this->addColumn('email', ['header' => __('Email'), 'index' => 'email']);
        $this->addColumn('name', ['header' => __('Customer Name'), 'index' => 'name']);

        return parent::_prepareColumns();
    }

    /**
     * get grid url
     *
     * @return void
     */
    public function getGridUrl()
    {
        return $this->getUrl('mppricelist/pricelist/customer', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function _getSelectedCustomers()
    {
        $customers = array_keys($this->getSelectedCustomers());
        return $customers;
    }

    /**
     * @return array
     */
    public function getSelectedCustomersJson()
    {
        $jsonCustomers = [];
        $customers = array_keys($this->getSelectedCustomers());
        if (!empty($customers)) {
            foreach ($customers as $customer) {
                $jsonCustomers[$customer] = 0;
            }
            return $this->_jsonEncoder->encode($jsonCustomers);
        }
        return '{}';
    }

    /**
     * @return array
     */
    public function getSelectedCustomers()
    {
        $allCustomers = $this->getRequest()->getPost('pricelist_customers');
        $customers = [];
        if ($allCustomers === null) {
            $priceList = $this->getPriceList();
            $id = $priceList->getId();
            $collection = $this->_detailsCollection
                                ->create()
                                ->addFieldToFilter("type", 1)
                                ->addFieldToFilter("pricelist_id", $id);
            foreach ($collection as $customer) {
                $customers[$customer->getUserId()] = ['position' => $customer->getUserId()];
            }
        } else {
            foreach ($allCustomers as $customer) {
                $customers[$customer] = ['position' => $customer];
            }
        }
        return $customers;
    }
}
