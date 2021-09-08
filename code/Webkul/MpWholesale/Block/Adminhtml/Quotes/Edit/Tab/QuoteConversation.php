<?php
/**
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Block\Adminhtml\Quotes\Edit\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Extended;
use Webkul\MpWholesale\Model\ResourceModel\Quoteconversation\Collection;
use Webkul\MpWholesale\Helper\Data;
use \Webkul\MpWholesale\Model\QuoteconversationFactory;

class QuoteConversation extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;
    /**
     * @var \Webkul\MpWholesale\Model\QuoteconversationFactory
     */
    protected $conversationFactory;
    /**
     * @var \Quoteconversation\Collection
     */
    protected $quoteconversationCollection;
    /**
     * @var Webkul\MpWholesale\Helper\Data
     */
    protected $helper;

    /**
     * @param \Template\Context             $context
     * @param \Magento\Backend\Helper\Data  $backendHelper
     * @param QuoteconversationFactory      $conversationFactory
     * @param \Magento\Framework\Registry   $coreRegistry
     * @param collection                    $quoteconversationCollection
     * @param Data                          $helper
     * @param array                         $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        QuoteconversationFactory $conversationFactory,
        \Magento\Framework\Registry $coreRegistry,
        Collection $quoteconversationCollection,
        Data $helper,
        array $data = []
    ) {
        $this->conversationFactory = $conversationFactory;
        $this->coreRegistry = $coreRegistry;
        $this->quoteconversationCollection = $quoteconversationCollection;
        $this->helper = $helper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quoteConversation_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $params = $this->getRequest()->getParams();
        $collection = $this->conversationFactory->create()->getCollection()
            ->addFieldToFilter(
                'quote_id',
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
            'sender_id',
            [
                'header'    => __('Sender'),
                'sortable'  => true,
                'index'     => 'sender_id',
                'filter'    =>  false,
                'renderer'  => \Webkul\MpWholesale\Block\Adminhtml\Quotes\Grid\RendererSenderName::class,
            ]
        );
        $this->addColumn(
            'receiver_id',
            [
                'header'    => __('Receiver'),
                'sortable'  => true,
                'index'     => 'receiver_id',
                'filter'    =>  false,
                'renderer'  => \Webkul\MpWholesale\Block\Adminhtml\Quotes\Grid\RendererReceiverName::class,
            ]
        );
        $this->addColumn(
            'conversation',
            [
                'header'    => __('Conversation'),
                'sortable'  => true,
                'index'     => 'conversation',
                'type'      => 'text',
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header'    => __('Created At'),
                'sortable'  => true,
                'index'     => 'created_at',
                'type'      => 'datetime',
                'renderer'  => \Webkul\MpWholesale\Block\Adminhtml\Quotes\Grid\RendererReceiverTime::class,
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
