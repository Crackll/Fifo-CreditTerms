<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Block\Adminhtml\Lead\Details\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('mpwholesale_leads_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Lead Information'));
    }

    /**
     * Prepare Layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $block = \Webkul\MpWholesale\Block\Adminhtml\Lead\Details\Edit\Tab\SentEmail::class;
        $this->addTab(
            'sent_mails',
            [
                'label' => __('Lead Details'),
                'content' => $this->getLayout()
                ->createBlock($block)
                ->setTemplate('Webkul_MpWholesale::leadDetails.phtml')
                ->toHtml(),
                'class' => 'ajax'
            ]
        );
        return parent::_prepareLayout();
    }
}
