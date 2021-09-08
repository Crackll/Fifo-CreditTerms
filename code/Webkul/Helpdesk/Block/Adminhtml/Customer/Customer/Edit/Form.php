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
namespace Webkul\Helpdesk\Block\Adminhtml\Customer\Customer\Edit;

/**
 * Adminhtml AccordionFaq Addfaq Edit Form
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
        \Webkul\Helpdesk\Model\CustomerFactory $hdcustomerFactory,
        \Webkul\Helpdesk\Model\CustomerOrganizationFactory $customerOrganizationFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_hdcustomerFactory = $hdcustomerFactory;
        $this->_customerFactory = $customerFactory;
        $this->_customerOrganizationFactory = $customerOrganizationFactory;
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
        $this->setId('customer_form');
        $this->setTitle(__('Customer Information'));
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

        $helpdeskCustomerModel = $this->_coreRegistry->registry('helpdesk_customer');

        $form->setHtmlIdPrefix('helpdesk_customer_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Add Customer'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);

        $fieldset->addField(
            'name',
            'text',
            [
                'name'  => 'name',
                'label' => __('Customer Name'),
                'title' => __('Customer Name'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'email',
            'text',
            [
                'name'  => 'email',
                'label' => __('Email'),
                'title' => __('Email'),
                'class'     => 'required-entry validate-email',
                'required' => true
            ]
        );

        $fieldset->addField(
            'customer_id',
            'select',
            [
                'label' => __('Select Customer'),
                'title' => __('Select Customer'),
                'name' => 'customer_id',
                'required' => false,
                'values' => $this->_getAllCustomer()
            ]
        );

        $fieldset->addField(
            'organizations',
            'select',
            [
                'label' => __('Select Organizations'),
                'title' => __('Select Organizations'),
                'name' => 'organizations',
                'required' => false,
                'values' => $this->_getAllOrganizations()
            ]
        );
        $form->setValues($helpdeskCustomerModel->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function _getAllCustomer()
    {
        $customerArray = [];
        $customerColl = $this->_customerFactory->create()->getCollection();
        array_push($customerArray, ["value"=>"" , "label"=>"Please Select"]);
        $fullname = "";
        foreach ($customerColl as $customerData) {
            $fullname = $customerData->getFirstname()." ".$customerData->getLastname();
            array_push($customerArray, ["value"=> $customerData->getId(), "label"=>$fullname]);
        }
        return $customerArray;
    }

    protected function _getAllOrganizations()
    {
        $orgArray = [];
        $orgColl = $this->_customerOrganizationFactory->create()->getCollection();
        array_push($orgArray, ["value"=>"" , "label"=>"Please Select"]);
        foreach ($orgColl as $orgData) {
            array_push($orgArray, ["value"=> $orgData->getId(), "label"=>$orgData->getName()]);
        }
        return $orgArray;
    }
}
