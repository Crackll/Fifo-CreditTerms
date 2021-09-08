<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Block\Adminhtml\Connectemail\Edit\Tab;

class Values extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @param \Webkul\Helpdesk\Model\TypeFactory $typeFactory,
     * @param \Webkul\Helpdesk\Model\GroupFactory $groupFactory
     * @param \Webkul\Helpdesk\Model\PriorityFactory $priorityFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Webkul\Helpdesk\Model\TypeFactory $typeFactory,
        \Webkul\Helpdesk\Model\GroupFactory $groupFactory,
        \Webkul\Helpdesk\Model\TicketsPriorityFactory $priorityFactory,
        array $data = []
    ) {
        $this->_typeFactory = $typeFactory;
        $this->_groupFactory = $groupFactory;
        $this->_priorityFactory = $priorityFactory;
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
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('connect_email_');
        $cEmailModel = $this->_coreRegistry->registry('helpdesk_connectemail');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Default Values of Ticket')]
        );

        $fieldset->addField(
            "default_group",
            "select",
            [
                "label" => __("Default Group"),
                "class" => "required-entry",
                "name" => "default_group",
                "values" => $this->_groupFactory->create()->toOptionArray()
            ]
        );

        $fieldset->addField(
            "default_type",
            "select",
            [
               "label" => __("Default Type"),
               "class" => "required-entry",
               "name" => "default_type",
               "values" => $this->_typeFactory->create()->toOptionArray()
            ]
        );

        $fieldset->addField(
            "default_priority",
            "select",
            [
               "label" => __("Default Priority"),
               "class" => "required-entry",
               "name" => "default_priority",
               "values" => $this->_priorityFactory->create()->toOptionArray()
            ]
        );

        $form->setValues($cEmailModel->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
