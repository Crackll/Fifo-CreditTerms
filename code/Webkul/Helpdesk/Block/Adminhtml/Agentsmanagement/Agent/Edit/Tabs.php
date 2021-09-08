<?php

/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 
namespace Webkul\Helpdesk\Block\Adminhtml\Agentsmanagement\Agent\Edit;

/**
 * Agent page left menu
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
        $this->setId('agent_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Agent Information'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $agentAddBlock = \Webkul\Helpdesk\Block\Adminhtml\Agentsmanagement\Agent\Edit\Tab\Form::class;
        $roleAddBlock = \Webkul\Helpdesk\Block\Adminhtml\Agentsmanagement\Agent\Edit\Tab\Role::class;
        $this->addTab(
            'main_section',
            [
                'label' => __('Agent'),
                'title' => __('Agent'),
                'content' => $this->getLayout()->createBlock($agentAddBlock)->toHtml(),
                'active' => true
            ]
        );
        $this->addTab(
            'roles_section',
            [
                'label' => __('Agent Role'),
                'title' => __('Agent Role'),
                'content' => $this->getLayout()->createBlock($roleAddBlock)->toHtml()
            ]
        );
        return parent::_beforeToHtml();
    }
}
