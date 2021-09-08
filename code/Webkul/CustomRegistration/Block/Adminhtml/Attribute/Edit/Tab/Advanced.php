<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomRegistration
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\CustomRegistration\Block\Adminhtml\Attribute\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Eav\Helper\Data;

class Advanced extends Generic
{
    /**
     * Eav data
     *
     * @var Data
     */
    protected $_eavData = null;

    /**
     * @var Yesno
     */
    protected $_yesNo;
    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory
     */
    protected $_inputTypeFactory;

    /**
     * @var array
     */
    protected $disableScopeChangeList;
    /**
     * @var \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker
     */
    protected $propertyLocker;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param Yesno $yesNo
     * @param Data $eavData
     * @param array $disableScopeChangeList
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory,
        \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker,
        Yesno $yesNo,
        Data $eavData,
        array $disableScopeChangeList = ['sku'],
        array $data = []
    ) {
        $this->_yesNo = $yesNo;
        $this->_eavData = $eavData;
        $this->propertyLocker = $propertyLocker;
        $this->_inputTypeFactory = $inputTypeFactory;
        $this->disableScopeChangeList = $disableScopeChangeList;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Adding product form elements for editing attribute
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $attributeObject = $this->getAttributeObject();

        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $form->setHtmlIdPrefix('dependable_');

        $fieldset = $form->addFieldset(
            'advanced_fieldset',
            ['legend' => __('Dependable Fields Properties'), 'collapsable' => true]
        );

        if ($attributeObject->getAttributeId()) {
            $fieldset->addField('attribute_id', 'hidden', ['name' => 'depend_attribute_id']);
        }

        $yesno = $this->_yesNo->toOptionArray();

        $labels = $attributeObject->getFrontendLabel();
        $fieldset->addField(
            'attribute_label',
            'text',
            [
                'name' => 'dependable_frontend_label',
                'label' => __('Default label'),
                'title' => __('Default label'),
                'required' => true,
                'value' => $labels
            ]
        );
        $validateClass = sprintf(
            'validate-code validate-length maximum-length-%d',
            \Magento\Eav\Model\Entity\Attribute::ATTRIBUTE_CODE_MAX_LENGTH
        );
        $fieldset->addField(
            'attribute_code',
            'text',
            [
                'name' => 'dependable_attribute_code',
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
        $options = $this->_inputTypeFactory->create()->toOptionArray();
        $notAllowed = ['texteditor', 'datetime', 'pagebuilder'];
        foreach ($options as $key => $value) {
            if (in_array($value['value'], $notAllowed)) {
                unset($options[$key]);
                continue;
            }
            $options[$key]['value'] = 'dependable_'.$value['value'];
        }
        $attributeObject->setFrontendInput('dependable_'.$attributeObject->getFrontendInput());
        $fieldset->addField(
            'frontend_input',
            'select',
            [
                'name' => 'dependable_frontend_input',
                'label' => __('Input Type for Store Owner'),
                'title' => __('Input Type for Store Owner'),
                'value' => 'text',
                'values' => $options
            ]
        );
        $fieldset->addField(
            'is_required',
            'select',
            [
                'name' => 'dependable_is_required',
                'label' => __('Values Required'),
                'title' => __('Values Required'),
                'values' => $yesno
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);

        $fieldset->addField(
            'frontend_class',
            'select',
            [
                'name' => 'dependable_frontend_class',
                'label' => __('Input Validation for Store Owner'),
                'title' => __('Input Validation for Store Owner'),
                'values' => $this->_eavData->getFrontendClasses($attributeObject->getEntityType()->getEntityTypeCode())
            ]
        );
        $frontendInputElm = $form->getElement('frontend_input');
        $additionalTypes = [
            ['value' => 'dependable_image', 'label' => __('Media Image')],
            ['value' => 'dependable_file', 'label' => __('File')],
        ];

        $frontendInputValues = array_merge($frontendInputElm->getValues(), $additionalTypes);
        $frontendInputElm->setValues($frontendInputValues);
         
        $this->propertyLocker->lock($form);
        $this->setForm($form);
        return $this;
    }

    /**
     * Initialize form fileds values
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $this->getForm()->addValues($this->getAttributeObject()->getData());
        return parent::_initFormValues();
    }

    /**
     * Retrieve attribute object from registry
     *
     * @return mixed
     */
    private function getAttributeObject()
    {
        return $this->_coreRegistry->registry('dependfields');
    }
}
