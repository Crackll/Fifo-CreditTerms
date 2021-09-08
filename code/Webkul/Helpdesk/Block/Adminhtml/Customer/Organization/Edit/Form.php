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
namespace Webkul\Helpdesk\Block\Adminhtml\Customer\Organization\Edit;

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
        \Webkul\Helpdesk\Model\GroupFactory $groupFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_groupFactory = $groupFactory;
        $this->_customerFactory = $customerFactory;
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
        $this->setTitle(__('Organization Information'));
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

        $form->setHtmlIdPrefix('helpdesk_org_');

        $customerOrganizationModel = $this->_coreRegistry->registry('customer_organization');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Add Organization'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);

        $fieldset->addField(
            'name',
            'text',
            [
                'name'  => 'name',
                'label' => __('Name'),
                'title' => __('Organization Name'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'description',
            'textarea',
            [
                'name'  => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'class'     => 'required-entry',
                'required' => true
            ]
        );

        $fieldset->addField(
            'domain',
            'text',
            [
                'name'  => 'domain',
                'label' => __('Domain'),
                'title' => __('Domain'),
                "class"     => "validate-url",
                'required' => false
            ]
        );

        $fieldset->addField(
            'notes',
            'textarea',
            [
                'name'  => 'notes',
                'label' => __('Notes'),
                'title' => __('Notes'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'customers',
            'multiselect',
            [
                'name'  => 'customers',
                'label' => __('Customer'),
                'title' => __('Customer'),
                'values'=> $this->_getAllCustomer(),
                'required' => false
            ]
        );

        $fieldset->addField(
            'customer_role',
            'select',
            [
                'label' => __('Customer Role'),
                'title' => __('Customer Role'),
                'name' => 'customer_role',
                'required' => false,
                'values' => ['0'=>'Disable', '1'=>'Enable']
            ]
        );

        $fieldset->addField(
            'groups',
            'multiselect',
            [
                'name'  => 'groups',
                'label' => __('Goups'),
                'title' => __('Goups'),
                'values'=> $this->_getAllGroups(),
                'required' => false
            ]
        );
        $form->setValues($customerOrganizationModel->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function _getAllCustomer()
    {
        $customerArray = [];
        $customerColl = $this->_customerFactory->create()->getCollection();
        $fullname = "";
        foreach ($customerColl as $customerData) {
            $fullname = $customerData->getFirstname()." ".$customerData->getLastname();
            array_push($customerArray, ["value"=> $customerData->getId(), "label"=>$fullname]);
        }
        return $customerArray;
    }

    protected function _getAllGroups()
    {
        $groupsArray = [];
        $groupColl = $this->_groupFactory->create()->getCollection();
        $fullname = "";
        foreach ($groupColl as $groupData) {
            array_push($groupsArray, ["value"=> $groupData->getId(), "label"=>$groupData->getGroupName()]);
        }
        return $groupsArray;
    }
}
