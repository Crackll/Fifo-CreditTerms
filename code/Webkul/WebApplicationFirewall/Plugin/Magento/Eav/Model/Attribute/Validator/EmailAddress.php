<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 *
 */
namespace Webkul\WebApplicationFirewall\Plugin\Magento\Eav\Model\Attribute\Validator;

use Webkul\WebApplicationFirewall\Helper\Data;
use Magento\Framework\DataObject;
use Webkul\WebApplicationFirewall\Model\Notificator;
use Webkul\WebApplicationFirewall\Integrations\ValidatorInterface as IntegrationValidator;
use Webkul\WebApplicationFirewall\Integrations\MailBoxLayerInterface;

/**
 * WAF EmailAddress class
 */
class EmailAddress
{
    /** @var Data */
    protected $helper;

    /**
     * @var IntegrationValidator
     */
    protected $integrationValidator;

    /**
     * @param Data $helper
     * @param Notificator $notificator
     * @param IntegrationValidator $integrationValidator
     */
    public function __construct(
        Data $helper,
        Notificator $notificator,
        IntegrationValidator $integrationValidator
    ) {
        $this->helper = $helper;
        $this->notificator = $notificator;
        $this->integrationValidator = $integrationValidator;
    }

    /**
     * Validate email in real time
     *
     * @param \Magento\Eav\Model\Attribute\Data\Text $subject
     * @param array|bool $result
     * @return array|bool
     */
    public function afterValidateValue(
        \Magento\Eav\Model\Attribute\Data\Text $subject,
        $result,
        $value
    ) {
        $errors = [];
        $attribute = $subject->getAttribute();
        $isEnabled = $this->helper->getConfigData(
            MailBoxLayerInterface::CODE,
            'customer'
        );
        if ($isEnabled && $result && $attribute->getAttributeCode() == 'email') {
            $this->integrationValidator->setType(MailBoxLayerInterface::CODE);
            $this->integrationValidator->setEmail($value);
            if (!$this->integrationValidator->validate()) {
                $errors[] =  __('"%1" is not a valid email address.', $value);
                $result = $errors;
            }
        }

        return $result;
    }
}
