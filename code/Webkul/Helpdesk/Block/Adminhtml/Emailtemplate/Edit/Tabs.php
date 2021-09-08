<?php
/**
 * Webkul Helpdesk Tickets Edit Tabs
 *
 * @category    Webkul
 * @package     Webkul_Helpdesk
 * @author      Webkul Software Private Limited
 *
 */

namespace Webkul\Helpdesk\Block\Adminhtml\Emailtemplate\Edit;

/**
 * Events page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Email Template Information'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'main_section',
            [
                'label' => __('Email Template Info'),
                'title' => __('Email Template Info'),
                'content' => $this->getLayout()->createBlock(
                    \Webkul\Helpdesk\Block\Adminhtml\Emailtemplate\Edit\Tab\Main::class
                )->toHtml(),
                'active' => true
            ]
        );

        return parent::_beforeToHtml();
    }
}
