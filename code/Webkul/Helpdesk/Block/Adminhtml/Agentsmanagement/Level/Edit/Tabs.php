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
 
namespace Webkul\Helpdesk\Block\Adminhtml\Agentsmanagement\Level\Edit;

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
        $this->setId('level_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Level Information'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $agentAddBlock = \Webkul\Helpdesk\Block\Adminhtml\Agentsmanagement\Level\Edit\Form::class;
        $this->addTab(
            'main_section',
            [
                'label' => __('Level'),
                'title' => __('Level'),
                'active' => true
            ]
        );
        return parent::_beforeToHtml();
    }
}
