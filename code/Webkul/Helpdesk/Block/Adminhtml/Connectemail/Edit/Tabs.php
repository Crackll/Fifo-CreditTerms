<?php
/**
 * Webkul Helpdesk Tickets Edit Tabs
 *
 * @category    Webkul
 * @package     Webkul_Helpdesk
 * @author      Webkul Software Private Limited
 *
 */

namespace Webkul\Helpdesk\Block\Adminhtml\Connectemail\Edit;

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
        $this->setTitle(__('Connect Email Information'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'main_section',
            [
                'label' => __('Connect Email Info'),
                'title' => __('Connect Email Info'),
                'content' => $this->getLayout()->createBlock(
                    \Webkul\Helpdesk\Block\Adminhtml\Connectemail\Edit\Tab\Main::class
                )->toHtml(),
                'active' => true
            ]
        );

        $this->addTab(
            'config_section',
            [
                'label' => __('IMAP / POP Configuration'),
                'title' => __('IMAP / POP Configuration'),
                'content' => $this->getLayout()->createBlock(
                    \Webkul\Helpdesk\Block\Adminhtml\Connectemail\Edit\Tab\Config::class
                )->toHtml(),
                'active' => false
            ]
        );

        $this->addTab(
            'values_section',
            [
                'label' => __('Default Values of Ticket'),
                'title' => __('Default Values of Ticket'),
                'content' => $this->getLayout()->createBlock(
                    \Webkul\Helpdesk\Block\Adminhtml\Connectemail\Edit\Tab\Values::class
                )->toHtml(),
                'active' => false
            ]
        );

        return parent::_beforeToHtml();
    }
}
