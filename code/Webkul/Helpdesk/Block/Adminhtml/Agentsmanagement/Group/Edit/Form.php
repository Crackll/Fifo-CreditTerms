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
namespace Webkul\Helpdesk\Block\Adminhtml\Agentsmanagement\Group\Edit;

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
        \Webkul\Helpdesk\Model\BusinesshoursFactory $businesshoursFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_businesshoursFactory = $businesshoursFactory;
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
        $this->setTitle(__('Group Information'));
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

        $form->setHtmlIdPrefix('agent_group_');

        $agentGroupModel = $this->_coreRegistry->registry('agent_group');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Add Group'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);

        $fieldset->addField(
            'group_name',
            'text',
            ['name' => 'group_name', 'label' => __('Group Name'), 'title' => __('Group Name'), 'required' => true]
        );

        $fieldset->addField(
            'businesshour_id',
            'select',
            [
                'label' => __('Business Hour'),
                'title' => __('Business Hour'),
                'name' => 'businesshour_id',
                'required' => true,
                'values' => $this->_getBusinessHours()
            ]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'is_active',
                'values' => ['1' => __('Enabled'), '2' => __('Disabled')]
            ]
        );

        $form->setValues($agentGroupModel->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function _getBusinessHours()
    {
        $businesshoursArray = [];
        $businesshours = $this->_businesshoursFactory->create();
        $businesshoursColl = $businesshours->getCollection();
        array_push($businesshoursArray, ["value"=>"", "label"=>"Please Select"]);
        foreach ($businesshoursColl as $businessData) {
            array_push(
                $businesshoursArray,
                ["value"=>$businessData->getId(), "label"=>$businessData->getBusinesshourName()]
            );
        }
        return $businesshoursArray;
    }
}
