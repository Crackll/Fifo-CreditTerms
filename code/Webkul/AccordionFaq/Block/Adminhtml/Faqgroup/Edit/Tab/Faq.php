<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AccordionFaq
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AccordionFaq\Block\Adminhtml\Faqgroup\Edit\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;

class Faq extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Webkul\Accordionfaq\Model\ImagesFactory
     */
    protected $_faqFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Webkul\Accordionfaq\Model\AddfaqFactory $faqFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Webkul\AccordionFaq\Model\AddfaqFactory $faqFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_faqFactory = $faqFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('accordionfaq_faqgroup_faq');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->_faqFactory->create()->getCollection()->addFieldToFilter("status", "1");
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_faqgroup',
            [
                'type' => 'checkbox',
                'name' => 'in_faqgroup',
                'values' => $this->_getSelectedFaq(),
                'index' => 'id'
            ]
        );
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        
        $this->addColumn(
            'faq',
            [
                'header' => __('FAQ'),
                'index' => 'faq'
            ]
        );
        $this->addColumn(
            'body',
            [
                'header' => __('Content'),
                'index' => 'body'
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('accordionfaq/*/faqGrid', ['_current' => true]);
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
    public function getFaqgroup()
    {
        return $this->_coreRegistry->registry('accordionfaq_faqgroup');
    }

    /**
     * @return array|null
     */
    protected function _getSelectedFaq()
    {
        $faq = array_keys($this->getSelectedGroupFaq());
        return $faq;
    }

    /**
     * @return array|null
     */
    public function getSelectedGroupFaq()
    {
        $faq = [];
        $faqIds = $this->getFaqgroup()->getFaqIds();
        $faqIds = explode(",", $faqIds);
        foreach ($faqIds as $faqId) {
            $faq[$faqId] = ['position' => $faqId];
        }
        return $faq;
    }

    /**
     * @return array|null
     */
    public function getFaqIds()
    {
        $faqIds = $this->getFaqgroup()->getFaqIds();
        return $faqIds;
    }

     /**
      * @return int
      */
    public function getFaqGroupId()
    {
        return $this->getFaqgroup()->getId();
    }
}
