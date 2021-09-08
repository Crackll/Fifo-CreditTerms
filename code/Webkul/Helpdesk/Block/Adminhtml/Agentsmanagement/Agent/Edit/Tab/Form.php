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
namespace Webkul\Helpdesk\Block\Adminhtml\Agentsmanagement\Agent\Edit\Tab;

/**
 * Adminhtml Helpdesk Ticket Status Edit Form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    const CURRENT_USER_PASSWORD_FIELD = 'current_password';

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
        \Magento\Backend\Model\Auth\Session $adminSession,
        \Webkul\Helpdesk\Model\AgentLevelFactory $agentLevelFactory,
        \Webkul\Helpdesk\Model\GroupFactory $groupFactory,
        \Magento\Config\Model\Config\Source\Locale\Timezone $timezone,
        array $data = []
    ) {
        $this->_adminSession = $adminSession;
        $this->_agentLevelFactory = $agentLevelFactory;
        $this->_groupFactory = $groupFactory;
        $this->_timezone = $timezone;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var $model \Magento\User\Model\User */
        $model = $this->_coreRegistry->registry('permissions_agent');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('agent_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Account Information')]);

        if ($model->getUserId()) {
            $fieldset->addField('user_id', 'hidden', ['name' => 'user_id']);
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        } else {
            if (!$model->hasData('is_active')) {
                $model->setIsActive(1);
            }
        }

        $fieldset->addField(
            'username',
            'text',
            [
                'name'      => 'username',
                'label'     => __('Username'),
                'title'     => __('Username'),
                'class'     => 'required-entry',
                'required'  => true
            ]
        );

        $fieldset->addField(
            'firstname',
            'text',
            [
                'name'      => 'firstname',
                'label'     => __('First Name'),
                'title'     => __('First Name'),
                'class'     => 'required-entry',
                'required'  => true
            ]
        );

        $fieldset->addField(
            'lastname',
            'text',
            [
                'name'      => 'lastname',
                'label'     => __('Last Name'),
                'title'     => __('Last Name'),
                'class'     => 'required-entry',
                'required'  => true
            ]
        );

        $fieldset->addField(
            'email',
            'text',
            [
                'name'      => 'email',
                'label'     => __('E-mail'),
                'title'     => __('E-mail'),
                'class'     => 'required-entry validate-email',
                'required'  => true
            ]
        );

        $isNewObject = $model->isObjectNew();
        if ($isNewObject) {
            $passwordLabel = __('Password');
        } else {
            $passwordLabel = __('New Password');
        }
        $confirmationLabel = __('Password Confirmation');
        $this->_addPasswordFields($fieldset, $passwordLabel, $confirmationLabel, $isNewObject);

        $fieldset->addField(
            'level',
            'select',
            [
                'name'      => 'level',
                'label'     => __('Level'),
                'title'     => __('Level'),
                'class'     => 'required-entry',
                'required'  => true,
                'values'    => $this->_getAllLevels()
            ]
        );

        if ($this->_adminSession->getUser()->getId() != $model->getUserId()) {
            $fieldset->addField(
                'is_active',
                'select',
                [
                    'name' => 'is_active',
                    'label' => __('This account is'),
                    'id' => 'is_active',
                    'title' => __('Account Status'),
                    'class' => 'input-select',
                    'options' => ['1' => __('Active'), '0' => __('Inactive')]
                ]
            );
        }

        $fieldset->addField(
            'timezone',
            'select',
            [
                'name'      => 'timezone',
                'label'     => __('Timezone'),
                'title'     => __('Timezone'),
                'class'     => 'required-entry',
                'required'  => true,
                'values'    => $this->_getAllTimeZones()
            ]
        );

        $fieldset->addField(
            'signature',
            'textarea',
            [
                'name' => 'signature',
                'label' => __('Signature'),
                'title' => __('Signature'),
                'wysiwyg'       =>      true,
                'required'      =>      false,
            ]
        );

        $fieldset->addField(
            'group_id',
            'select',
            [
                'name'      => 'group_id',
                'label'     => __('Agent Group'),
                'title'     => __('Agent Group'),
                'class'     => 'required-entry',
                'required'  => true,
                'values'    => $this->_getAllGroups()
            ]
        );

        $fieldset->addField(
            'ticket_scope',
            'select',
            [
                'name'      => 'ticket_scope',
                'label'     => __('Ticket Scope'),
                'title'     => __('Ticket Scope'),
                'class'     => 'required-entry',
                'required'  => false,
                'values'    => [
                                '0' => __('Please Select'),
                                '1' => __('Global Access'),
                                '2' => __('Group Access'),
                                '3' => __('Restricted Access')
                            ]
            ]
        );

        $currentUserVerificationFieldset = $form->addFieldset(
            'current_user_verification_fieldset',
            ['legend' => __('Current User Identity Verification')]
        );

        $currentUserVerificationFieldset->addField(
            self::CURRENT_USER_PASSWORD_FIELD,
            'password',
            [
                'name' => self::CURRENT_USER_PASSWORD_FIELD,
                'label' => __('Your Password'),
                'id' => self::CURRENT_USER_PASSWORD_FIELD,
                'title' => __('Your Password'),
                'class' => 'input-text validate-current-password required-entry',
                'required' => true
            ]
        );

        $data = $model->getData();
        unset($data['password']);
        unset($data[self::CURRENT_USER_PASSWORD_FIELD]);
        $form->setValues($data);

        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function _getAllLevels()
    {
        $levelsArray = [];
        $levels = $this->_agentLevelFactory->create()->getCollection()->addFieldToFilter("status", ["eq"=>1]);
        array_push($levelsArray, ["value" => "", "label" => "Please Select"]);
        foreach ($levels as $levelData) {
            array_push($levelsArray, ["value" => $levelData->getId(), "label" => $levelData->getName()]);
        }
        return $levelsArray;
    }

    protected function _getAllGroups()
    {
        $groupsArray = [];
        $groups = $this->_groupFactory->create()->getCollection();
        array_push($groupsArray, ["value" => "", "label" => "Please Select"]);
        foreach ($groups as $groupData) {
            array_push($groupsArray, ["value" => $groupData->getId(), "label" => $groupData->getGroupName()]);
        }
        return $groupsArray;
    }

    protected function _getAllTimeZones()
    {
        $timezonesArray = [];
        $timezones = $this->_timezone->toOptionArray();
        array_push($timezonesArray, ["value" => "", "label" => "Please Select"]);
        foreach ($timezones as $timezoneData) {
            array_push($timezonesArray, ["value" => $timezoneData['value'], "label" => $timezoneData['label']]);
        }
        return $timezonesArray;
    }

    /**
     * Add password input fields
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param string $passwordLabel
     * @param string $confirmationLabel
     * @param bool $isRequired
     * @return void
     */
    protected function _addPasswordFields(
        \Magento\Framework\Data\Form\Element\Fieldset $fieldset,
        $passwordLabel,
        $confirmationLabel,
        $isRequired = false
    ) {
        $requiredFieldClass = $isRequired ? ' required-entry' : '';
        $fieldset->addField(
            'password',
            'password',
            [
                'name' => 'password',
                'label' => $passwordLabel,
                'id' => 'customer_pass',
                'title' => $passwordLabel,
                'class' => 'input-text validate-admin-password' . $requiredFieldClass,
                'required' => $isRequired
            ]
        );
        $fieldset->addField(
            'confirmation',
            'password',
            [
                'name' => 'password_confirmation',
                'label' => $confirmationLabel,
                'id' => 'confirmation',
                'title' => $confirmationLabel,
                'class' => 'input-text validate-cpassword' . $requiredFieldClass,
                'required' => $isRequired
            ]
        );
    }
}
