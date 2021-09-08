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
namespace Webkul\MpRmaSystem\Block\Adminhtml\Rma\View\Tab;

use Webkul\MpRmaSystem\Model\ResourceModel\Conversation\CollectionFactory;

class Conversation extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var CollectionFactory
     */
    protected $conversationCollection;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param CollectionFactory $conversationCollection
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        CollectionFactory $conversationCollection,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->conversationCollection = $conversationCollection;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('mprmasystem_rma_conversation');
        $this->setDefaultSort('created_time');
        $this->setUseAjax(true);
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $rmaId = $this->getRma()->getId();
        $collection = $this->conversationCollection
                            ->create()
                            ->addFieldToFilter("rma_id", $rmaId);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $senderTypes = ['Admin', 'Seller', 'Customer', 'Guest'];
        $this->addColumn(
            'sender_type',
            [
                'header' => __('Sender Type'),
                'index' => 'sender_type',
                'type'      => 'options',
                'options'   => $senderTypes
            ]
        );
        $this->addColumn(
            'message',
            [
                'header' => __('Message'),
                'index' => 'message'
            ]
        );
        $this->addColumn(
            'created_time',
            [
                'header' => __('Date'),
                'index' => 'created_time',
                'type'      => 'datetime'
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/conversationGrid', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getRowUrl($row)
    {
        return "javascript:void(0)";
    }

    /**
     * @return array|null
     */
    public function getRma()
    {
        return $this->coreRegistry->registry('mprmasystem_rma');
    }
}
