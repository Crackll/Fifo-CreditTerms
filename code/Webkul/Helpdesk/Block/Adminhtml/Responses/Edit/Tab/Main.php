<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Block\Adminhtml\Responses\Edit\Tab;

class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Webkul\Helpdesk\Model\GroupFactory $groupFactory,
        array $data = []
    ) {
        $this->_groupFactory = $groupFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form fields
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return \Magento\Backend\Block\Widget\Form
     */
    public function _prepareForm()
    {
        /** @var $model \Magento\User\Model\User */
        $responsemodel = $this->_coreRegistry->registry('helpdesk_responses');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('response_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Add Response'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);

        $fieldset->addField(
            'name',
            'text',
            ['name' => 'name', 'label' => __('Name'), 'title' => __('Name'), 'required' => true]
        );

        $fieldset->addField(
            'description',
            'textarea',
            [
                'name' => 'description',
                'label' => __('Description'),
                'id' => 'description',
                'title' => __('Description'),
                'class' => 'required-entry',
                'required' => true
            ]
        );

        $action = $fieldset->addField(
            'can_use',
            'select',
            [
                'label' => __('Can Use'),
                'title' => __('Can Use'),
                'name' => 'can_use',
                'required' => true,
                'options' => [
                    "me" => __("Me"),
                    "all" => __("All Agents"),
                    "groups" => __("Group")
                ]
            ]
        );

        $anotherField = $fieldset->addField(
            "groups",
            "multiselect",
            [
                "label"     => __("Groups"),
                "class"     => "required-entry",
                "required"  => true,
                "name"      => "groups",
                'values' => $this->_groupFactory->create()->toOptionArray()
            ]
        );

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Form\Element\Dependence::class)
                ->addFieldMap($action->getHtmlId(), $action->getName())
                ->addFieldMap($anotherField->getHtmlId(), $anotherField->getName())
                ->addFieldDependence($anotherField->getName(), $action->getName(), 'groups')
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'required' => true,
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
            ]
        );

        $form->setValues($responsemodel->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
