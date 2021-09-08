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

class Config extends \Magento\Backend\Block\Widget\Form\Generic
{
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

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('IMAP / POP Configuration')]
        );

        /** @var $model \Magento\User\Model\User */
        $cEmailModel = $this->_coreRegistry->registry('helpdesk_connectemail');

        $fieldset->addField(
            "username",
            "text",
            [
                "label"     =>      __("User Name"),
                "class"     =>      "required-entry",
                "required"  =>      true,
                "name"      =>      "username"
            ]
        );

        $fieldset->addField(
            "password",
            "password",
            [
                "label"     =>      __("Password"),
                "class"     =>      "required-entry",
                "required"  =>      true,
                "name"      =>      "password"
            ]
        );

        $fieldset->addField(
            "host_name",
            "text",
            [
                "label"     =>      __("Host Name"),
                "class"     =>      "required-entry",
                "required"  =>      true,
                "name"      =>      "host_name"
            ]
        );

        $fieldset->addField(
            "port",
            "text",
            [
                "label"     =>      __("port"),
                "class"     =>      "required-entry validate-number",
                "required"  =>      true,
                "name"      =>      "port"
            ]
        );

        $fieldset->addField(
            "mailbox",
            "text",
            [
                "label"     =>      __("Mailbox"),
                "class"     =>      "required-entry",
                "required"  =>      true,
                "name"      =>      "mailbox"
            ]
        );

        $fieldset->addField(
            "protocol",
            "select",
            [
                "label"     =>      __("Protocol"),
                "class"     =>      "required-entry",
                "name"      =>      "protocol",
                "required"  =>      true,
                "values"    =>      [
                    ["value" => "","label" => __("Select Protocol")],
                    ["value" => "IMAP","label" => __("IMAP")],
                    ["value" => "POP","label" => __("POP")]
                ]
            ]
        );

        $fieldset->addField(
            "fetch_email_limit",
            "text",
            [
                "label"     =>      __("Fetch Emails (no.)"),
                "class"     =>      "required-entry",
                "required"  =>      true,
                "name"      =>      "fetch_email_limit"
            ]
        );

        $fieldset->addField("count", "hidden", ['name'=>'count']);
            
        $action = $fieldset->addField(
            "helpdesk_action",
            "select",
            [
                "label"     =>      __("Helpdesk Action"),
                "class"     =>      "required-entry",
                "name"      =>      "helpdesk_action",
                "values"    =>      [
                    ["value" => 0,"label" => __("Do Nothing")],
                    ["value" => 1,"label" => __("Delete Email(s)")],
                    ["value" => 2,"label" => __("Create Folder")]
                ]
            ]
        );

        $anotherField = $fieldset->addField(
            "mailbox_folder",
            "text",
            [
                "label"     => __("Move Email(s)"),
                "class"     => "required-entry",
                "required"  => true,
                "name"      => "mailbox_folder"
            ]
        );

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Form\Element\Dependence::class)
                ->addFieldMap($action->getHtmlId(), $action->getName())
                ->addFieldMap($anotherField->getHtmlId(), $anotherField->getName())
                ->addFieldDependence($anotherField->getName(), $action->getName(), 2)
        );

        $form->setValues($cEmailModel->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
