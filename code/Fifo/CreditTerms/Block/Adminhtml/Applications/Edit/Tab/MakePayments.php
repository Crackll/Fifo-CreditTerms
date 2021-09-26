<?php

namespace Fifo\CreditTerms\Block\Adminhtml\Applications\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class MakePayments extends Generic implements TabInterface
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Credit Term Make Payment');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Credit Term Make Payment');
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


    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_fifo_creditterms_applications');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('applications_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Credit Term Applications Information')]);
        if ($model->getId()) {
            $fieldset->addField('creditterms_application_id', 'hidden', ['name' => 'creditterms_application_id']);
        }

        $fieldset->addField(
            'make_payment',
            'text',
            [
                'name' => 'make_payment',
                'label' => __('Make Payment'),
                'title' => __('Make Payment'),
                'required' => false
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat();
        $fieldset->addField(
            'date_of_payment',
            'date',
            [
                'name' => 'date_of_payment',
                'label' => __('Date of Payment'),
                'title' => __('Date of Payment'),
                'required' => false,
                'date_format' => $dateFormat
            ]
        );

        $fieldset->addField(
            'payment_reference',
            'text',
            [
                'name' => 'payment_reference',
                'label' => __('Payment Reference'),
                'title' => __('Payment Reference'),
                'required' => false
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();

    }
}
