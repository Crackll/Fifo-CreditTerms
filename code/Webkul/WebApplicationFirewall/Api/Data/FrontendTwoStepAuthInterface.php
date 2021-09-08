<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\WebApplicationFirewall\Api\Data;

interface FrontendTwoStepAuthInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * webkul_frontend_two_step_auth table fields
     */
    const CUSTOMER_EMAIL = 'customer_email';
    const SECRET_KEY = 'secret_key';
    const IS_AUTH_ENABLED = 'is_auth_enabled';
    const CREATED_AT = 'created_at';
    const CUSTOMER_ID = 'customer_id';
    const ENTITY_ID = 'entity_id';
    const DEVICES_DATA = 'devices_data';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface
     */
    public function setEntityId($entityId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthExtensionInterface $extensionAttributes
    );

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get customer email
     * @return string|null
     */
    public function getCustomerEmail();

    /**
     * Set customer email
     * @param string $email
     * @return \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface
     */
    public function setCustomerEmail($email);

    /**
     * Get secret_key
     * @return string|null
     */
    public function getSecretKey();

    /**
     * Set secret_key
     * @param string $secretKey
     * @return \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface
     */
    public function setSecretKey($secretKey);

    /**
     * Get devices data
     * @return string|null
     */
    public function getDevicesData();

    /**
     * Set devices data
     * @param string $devicesData
     * @return \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface
     */
    public function setDevicesData($devicesData);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * is auth enabled
     * @return string|null
     */
    public function getIsAuthEnabled();

    /**
     * set auth enabled
     * @param string $isAuthEnabled
     * @return \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface
     */
    public function setIsAuthEnabled($isAuthEnabled);
}
