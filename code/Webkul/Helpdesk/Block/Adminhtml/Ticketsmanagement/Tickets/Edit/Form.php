<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Block\Adminhtml\Ticketsmanagement\Tickets\Edit;

/**
 * Adminhtml Helpdesk Tickets Edit Form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
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
        \Webkul\Helpdesk\Model\TypeFactory $typeFactory,
        array $data = []
    ) {
        $this->_typeFactory = $typeFactory;
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {

        parent::_construct();
        $this->setId('tickets_form');
        $this->setTitle(__('Ticket Information'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
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
            ['legend' => __('Add Ticket'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'fullname',
            'text',
            ['name' => 'fullname', 'label' => __('Customer Name'), 'title' => __('Customer Name'), 'required' => true]
        );

        $fieldset->addField(
            'email',
            'text',
            [
                'name' => 'email',
                'label' => __('Email'),
                'title' => __('Email'),
                'required' => true,
                'class' => 'required-entry validate-email'
            ]
        );

        $fieldset->addField(
            'type',
            'select',
            [
                'name' => 'type',
                'label' => __('Type'),
                'id' => 'type',
                'title' => __('Type'),
                'values' => $this->getAllTicketType(),
                'class' => 'required-entry',
                'required' => true
            ]
        );

        $fieldset->addField(
            'subject',
            'text',
            ['name' => 'subject', 'label' => __('Subject'), 'title' => __('Subject'), 'required' => true]
        );

        $fieldset->addField(
            'query',
            'textarea',
            [
                'name' => 'query',
                'label' => __('Query'),
                'id' => 'query',
                'title' => __('Query'),
                'class' => 'required-entry',
                'required' => true
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getAllTicketType()
    {
        $typeArray = [];
        $typeData = $this->_typeFactory->create()->getCollection()
            ->addFieldToFilter(
                "status",
                [
                "eq"=>1
                ]
            )
            ->addFieldToSelect('*')->getData();
        foreach ($typeData as $value) {
            $typeArray[$value['entity_id']] = $value['type_name'];
        }
        return $typeArray;
    }
}
