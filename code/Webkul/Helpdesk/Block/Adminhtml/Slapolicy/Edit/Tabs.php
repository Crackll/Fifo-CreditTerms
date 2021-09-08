<?php
/**
 * Webkul Helpdesk Tickets Edit Tabs
 *
 * @category    Webkul
 * @package     Webkul_Helpdesk
 * @author      Webkul Software Private Limited
 *
 */

namespace Webkul\Helpdesk\Block\Adminhtml\Slapolicy\Edit;

/**
 * Slapolicy page left menu
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
        $this->setTitle(__('SLA Policy'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'main_section',
            [
                'label' => __('SLA Policy'),
                'title' => __('SLA Policy'),
                'content' => $this->getLayout()->createBlock(
                    \Webkul\Helpdesk\Block\Adminhtml\Slapolicy\Edit\Tab\Main::class
                )->toHtml(),
                'active' => true
            ]
        );

        $this->addTab(
            'target_section',
            [
                'label' => __('SLA Targets'),
                'title' => __('SLA Targets'),
                'content' => $this->getLayout()->createBlock(
                    \Webkul\Helpdesk\Block\Adminhtml\Slapolicy\Edit\Tab\Targets::class
                )->toHtml(),
                'active' => false
            ]
        );

        $this->addTab(
            'condition_section',
            [
                'label' => __('Conditions for SLA'),
                'title' => __('Conditions for SLA'),
                'content' => $this->getLayout()->createBlock(
                    \Webkul\Helpdesk\Block\Adminhtml\Slapolicy\Edit\Tab\Condition::class
                )->toHtml(),
                'active' => false
            ]
        );

        return parent::_beforeToHtml();
    }
}
