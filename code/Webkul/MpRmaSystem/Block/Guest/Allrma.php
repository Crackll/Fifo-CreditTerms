<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRmaSystem\Block\Guest;

use Webkul\MpRmaSystem\Model\ResourceModel\Details\CollectionFactory;

class Allrma extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\MpRmaSystem\Helper\Data
     */
    protected $mpRmaHelper;

    /**
     * @var CollectionFactory
     */
    protected $detailsCollection;

    /**
     * @var Webkul\MpRmaSystem\Model\ResourceModel\Details\Collection
     */
    protected $rma;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper
     * @param CollectionFactory $detailsCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper,
        CollectionFactory $detailsCollection,
        array $data = []
    ) {
        $this->mpRmaHelper = $mpRmaHelper;
        $this->detailsCollection = $detailsCollection;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('RMA Panel'));
    }

    /**
     * @return bool | collection object
     */
    public function getAllRma()
    {
        $email = $this->mpRmaHelper->getGuestEmailId();
        if (!$this->rma) {
            $collection = $this->detailsCollection
                                ->create()
                                ->addFieldToFilter('customer_id', 0)
                                ->addFieldToFilter('customer_email', $email);
            $this->rma = $collection;
        }

        $this->applyFilter();
        $sortingOrder = $this->mpRmaHelper->getSortingOrder();
        $sortingField = $this->mpRmaHelper->getSortingField();
        $this->rma->setOrder($sortingField, $sortingOrder);

        return $this->rma;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllRma()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'mprmasystem.rma.list.pager'
            )->setCollection(
                $this->getAllRma()
            );
            $this->setChild('pager', $pager);
            $this->getAllRma()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function applyFilter()
    {
        $this->rma = $this->mpRmaHelper->applyFilter($this->rma);
    }

    public function helper()
    {
        return $this->mpRmaHelper;
    }
}
