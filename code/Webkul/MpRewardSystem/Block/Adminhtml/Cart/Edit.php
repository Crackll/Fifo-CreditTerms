<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpRewardSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MpRewardSystem\Block\Adminhtml\Cart;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry           $registry
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'Webkul_MpRewardSystem';
        $this->_controller = 'adminhtml_cart';
        parent::_construct();
        if ($this->_isAllowedAction('Webkul_MpRewardSystem::cart')) {
            $this->buttonList->update('save', 'label', __('Save Cart Rule'));
            $this->buttonList->add(
                'my_back',
                [
                    'label' =>  'Back',
                    'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/index') . '\')',
                    'class'     =>  'back'
                ]
            );
            $this->buttonList->remove('back');
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' =>
                                [
                                    'event' => 'saveAndContinueEdit',
                                    'target' => '#edit_form'
                                ],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }
    }

    /**
     * Retrieve text for header element depending on loaded post
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $codRegistry = $this->coreRegistry->registry('reward_cartrewardData');
        $codPrice = $this->escapeHtml($codRegistry);
        if ($codPrice->getEntityId()) {
            return __("Edit Rule");
        } else {
            return __('New Rule');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
    /**
     * Getter of url for "Save and Continue" button
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            '*/*/save',
            ['_current' => true,
                'back' => 'edit',
                'active_tab' => ''
            ]
        );
    }
}
