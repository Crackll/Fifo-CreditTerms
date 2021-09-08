<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Helpdesk\Block\Adminhtml\Ticketsmanagement\Attribute\Edit\Tab;

class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Config\Model\Config\Source\YesnoFactory
     */
    protected $_yesnoFactory;

    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory
     */
    protected $_inputTypeFactory;

    /**
     * Attribute instance
     *
     * @var Attribute
     */
    protected $_attribute = null;

    /**
     * @var \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker
     */
    protected $propertyLocker;

    /**
     * Eav data
     *
     * @var \Magento\Eav\Helper\Data
     */
    protected $_eavData = null;

    /**
     * @param \Magento\Eav\Helper\Data $eavData
     * @param \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory
     * @param \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Eav\Helper\Data $eavData,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory,
        \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        \Webkul\Helpdesk\Model\TypeFactory $typeFactory,
        \Magento\Eav\Model\Entity $eavEntity,
        array $data = []
    ) {
        $this->propertyLocker = $propertyLocker;
        $this->_eavData = $eavData;
        $this->_yesnoFactory = $yesnoFactory;
        $this->_inputTypeFactory = $inputTypeFactory;
        $this->yesNo = $yesNo;
        $this->typeFactory = $typeFactory;
        $this->_eavEntity = $eavEntity;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Preparing default form elements for editing attribute
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $attributemodel = $this->_coreRegistry->registry('helpdesk_ticket_attribute');

        $form = $this->_formFactory->create(
            ['data' => [
                        'id' => 'edit_form',
                        'enctype' => 'multipart/form-data',
                        'action' => $this->getData('action'),
                        'method' => 'post']
                    ]
        );

        $form->setHtmlIdPrefix('ts_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Add Attribute'), 'class' => 'fieldset-wide']
        );

        if ($attributemodel->getId()) {
            $fieldset->addField(
                'attribute_id',
                'hidden',
                ['name' => 'attribute_id']
            );
        }

        $fieldset->addField('entity_type_id', 'hidden', [
            'name'      =>      'entity_type_id',
            'value'     =>      $this->_eavEntity->setType("ticketsystem_ticket")->getTypeId()
        ]);

        $fieldset->addField('is_user_defined', 'hidden', [
            'name'      =>      'is_user_defined',
            'value'     =>      1
        ]);

        $fieldset->addField('attribute_set_id', 'hidden', [
            'name'      =>      'attribute_set_id',
            'value'     =>      $this->_eavEntity->setType("ticketsystem_ticket")->getTypeId()
        ]);

        $fieldset->addField('attribute_group_id', 'hidden', [
            'name'      =>      'attribute_group_id',
            'value'     =>      $this->_eavEntity->setType("ticketsystem_ticket")->getTypeId()
        ]);

        $yesNo = $this->yesNo->toOptionArray();

        $fieldset->addField(
            'attribute_label',
            'text',
            [
                'name' => 'frontend_label',
                'label' => __('Default label'),
                'title' => __('Default label'),
                'required' => true
            ]
        );

        $fieldDepend = $this->typeFactory->create()->getCollection();
        $fieldsArray[0] = __('Please Select');
        foreach ($fieldDepend as $fieldKey => $fieldData) {
            $fieldsArray[$fieldData->getId()] = $fieldData->getTypeName();
        }

        $validateClass = sprintf(
            'validate-code validate-length maximum-length-%d',
            \Magento\Eav\Model\Entity\Attribute::ATTRIBUTE_CODE_MAX_LENGTH
        );

        $fieldset->addField(
            'attribute_code',
            'text',
            [
                'name' => 'attribute_code',
                'label' => __('Attribute Code'),
                'title' => __('Attribute Code'),
                'note' => __(
                    'This is used internally. Make sure you don\'t use spaces or more than %1 characters.',
                    \Magento\Eav\Model\Entity\Attribute::ATTRIBUTE_CODE_MAX_LENGTH
                ),
                'class' => $validateClass,
                'required' => true
            ]
        );

        $fieldset->addField(
            'field_dependency',
            'select',
            [
                'name' => 'field_dependency',
                'label' => __('Field Dependency'),
                'title' => __('Field Dependency'),
                'value' => 'text',
                'values' => $fieldsArray
            ]
        );

        $options = $this->_inputTypeFactory->create()->toOptionArray();
        foreach ($options as $key => $value) {
            $options[$key]['value'] = $value['value'];
        }

        $fieldset->addField(
            'frontend_input',
            'select',
            [
                'name' => 'frontend_input',
                'label' => __('Input Type'),
                'title' => __('Input Type'),
                'value' => 'text',
                'values' => $options
            ]
        );

        $fieldset->addField(
            'is_required',
            'select',
            [
                'name' => 'is_required',
                'label' => __('Values Required'),
                'title' => __('Values Required'),
                'values' => $yesNo
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);

        $fieldset->addField(
            'frontend_class',
            'select',
            [
                'name' => 'frontend_class',
                'label' => __('Input Validation'),
                'title' => __('Input Validation'),
                'values' => $this->_eavData->getFrontendClasses(1)
            ]
        );

        $fieldset->addField(
            'is_visible',
            'select',
            [
                'name' => 'is_visible',
                'label' => __('Status'),
                'title' => __('Status'),
                'values' => [['value' => 1, 'label' => __('Enable')], ['value' => 2, 'label' => __('Disable')]]
            ]
        );

        $frontendInputElm = $form->getElement('frontend_input');
        $additionalTypes = [
            ['value' => 'image', 'label' => __('Media Image')],
            ['value' => 'file', 'label' => __('File')],
        ];
        $frontendInputValues = array_merge($frontendInputElm->getValues(), $additionalTypes);
        $frontendInputElm->setValues($frontendInputValues);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Initialize form fileds values
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $attributemodel = $this->_coreRegistry->registry('helpdesk_ticket_attribute');
        $this->_eventManager->dispatch(
            'adminhtml_block_eav_attribute_edit_form_init',
            ['form' => $this->getForm()]
        );
        $this->getForm()->addValues($attributemodel->getData());
        return parent::_initFormValues();
    }

    /**
     * Prepare the layout.
     *
     * @return $this
     */
    public function getFormHtml()
    {
        $html = parent::getFormHtml();
        $html .= $this->getLayout()->createBlock(
            \Webkul\Helpdesk\Block\Adminhtml\Ticketsmanagement\Attribute\Edit\Options::class
        )->toHtml();
        return $html;
    }

    /**
     * Processing block html after rendering
     * Adding js block to the end of this block
     *
     * @param   string $html
     * @return  string
     */
    protected function _afterToHtml($html)
    {
        $jsScripts = $this->getLayout()->createBlock(\Magento\Eav\Block\Adminhtml\Attribute\Edit\Js::class)->toHtml();
        return $html . $jsScripts;
    }
}
