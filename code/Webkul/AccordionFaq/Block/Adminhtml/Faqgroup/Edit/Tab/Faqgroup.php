<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AccordionFaq
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AccordionFaq\Block\Adminhtml\Faqgroup\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Widget\Form\Generic;

class Faqgroup extends Generic implements TabInterface
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('accordionfaq_faqgroup');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('faqgroup_');
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('FAQ Group Information'), 'class' => 'fieldset-wide']
            );
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
            $fieldset->addField(
                'group_code',
                'text',
                [
                    'name' => 'group_code',
                    'label' => __('Group Code'),
                    'title' => __('Group Code'),
                    'required' => true,
                    'note'      => __('Enter code for group'),
                ]
            );
            $fieldset->addField(
                'group_name',
                'text',
                [
                    'name' => 'group_name',
                    'label' => __('Group Name'),
                    'title' => __('Group Name'),
                    'required' => true,
                    'note'      => __('Enter group name')
                ]
            );
            $fieldset->addField(
                'width',
                'text',
                [
                    'name' => 'width',
                    'label' => __('width'),
                    'title' => __('width'),
                    'required' => true,
                    'class' => 'validate-digits',
                    'note'      => __('Enter width of the group in the format of pixels')
                ]
            );
            $fieldset->addField(
                'status',
                'select',
                [
                    'label' => __('Status'),
                    'title' => __('Status'),
                    'name' => 'status',
                    'required' => true,
                    'options' => ['1' => __('Enabled'), '0' => __('Disabled')],

                ]
            );
            $form->setValues($model->getData());
            $this->setForm($form);
            return parent::_prepareForm();
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
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('faqgroup Data');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('faqgroup Data');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
