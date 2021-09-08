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
namespace Webkul\AccordionFaq\Block\Adminhtml\Faqgroup\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('faqgroup_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('FAQ Group Information'));
    }

    /**
     * Prepare Layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addTab(
            'faqgroup',
            [
                'label' => __('FAQ Group'),
                'content' => $this->getLayout()->createBlock(
                    \Webkul\AccordionFaq\Block\Adminhtml\Faqgroup\Edit\Tab\Faqgroup::class,
                    'faqgroup'
                )->toHtml(),
            ]
        );
        $this->addTab(
            'faq',
            [
                'label' => __('FAQ'),
                'url' => $this->getUrl('accordionfaq/*/faq', ['_current' => true]),
                'class' => 'ajax'
            ]
        );
        return parent::_prepareLayout();
    }
}
