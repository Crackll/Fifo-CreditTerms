<?php
/**
 * Webkul Helpdesk Tickets Edit Tabs
 *
 * @category    Webkul
 * @package     Webkul_Helpdesk
 * @author      Webkul Software Private Limited
 *
 */

namespace Webkul\Helpdesk\Block\Adminhtml\Businesshours\Edit;

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
        $this->setTitle(__('Businesshours Information'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'main_section',
            [
                'label' => __('Businesshours Info'),
                'title' => __('Businesshours Info'),
                'content' => $this->getLayout()->createBlock(
                    \Webkul\Helpdesk\Block\Adminhtml\Businesshours\Edit\Tab\Main::class
                )->toHtml(),
                'active' => true
            ]
        );

        $this->addTab(
            'hours_section',
            [
                'label' => __('Helpdesk Hours'),
                'title' => __('Helpdesk Hours'),
                'content' => $this->getLayout()->createBlock(
                    \Webkul\Helpdesk\Block\Adminhtml\Businesshours\Edit\Tab\Hours::class
                )->toHtml(),
                'active' => false
            ]
        );

        $this->addTab(
            'holidays_section',
            [
                'label' => __('Yearly Holiday List'),
                'title' => __('Yearly Holiday List'),
                'content' => $this->getLayout()->createBlock(
                    \Webkul\Helpdesk\Block\Adminhtml\Businesshours\Edit\Tab\Holidays::class
                )->toHtml(),
                'active' => false
            ]
        );

        return parent::_beforeToHtml();
    }
}
