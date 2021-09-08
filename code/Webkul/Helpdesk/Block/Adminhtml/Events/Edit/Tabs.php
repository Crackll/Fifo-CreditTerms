<?php
/**
 * Webkul Helpdesk Tickets Edit Tabs
 *
 * @category    Webkul
 * @package     Webkul_Helpdesk
 * @author      Webkul Software Private Limited
 *
 */

namespace Webkul\Helpdesk\Block\Adminhtml\Events\Edit;

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
        $this->setTitle(__('Events Information'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'main_section',
            [
                'label' => __('Events Info'),
                'title' => __('Events Info'),
                'content' => $this->getLayout()->createBlock(
                    \Webkul\Helpdesk\Block\Adminhtml\Events\Edit\Tab\Main::class
                )->toHtml(),
                'active' => true
            ]
        );

        $this->addTab(
            'action_section',
            [
                'label' => __('Action'),
                'title' => __('Action'),
                'content' => $this->getLayout()->createBlock(
                    \Webkul\Helpdesk\Block\Adminhtml\Events\Edit\Tab\Action::class
                )->toHtml(),
                'active' => false
            ]
        );

        return parent::_beforeToHtml();
    }
}
