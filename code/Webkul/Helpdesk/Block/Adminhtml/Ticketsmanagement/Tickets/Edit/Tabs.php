<?php
/**
 * Webkul Helpdesk Tickets Edit Tabs
 *
 * @category    Webkul
 * @package     Webkul_Helpdesk
 * @author      Webkul Software Private Limited
 *
 */

namespace Webkul\Helpdesk\Block\Adminhtml\Ticketsmanagement\Tickets\Edit;

/**
 * Tickets page left menu
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
        $this->setTitle(__('Ticket Information'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'main_section',
            [
                'label' => __('Ticket Info'),
                'title' => __('Ticket Info'),
                'active' => true
            ]
        );
        return parent::_beforeToHtml();
    }
}
