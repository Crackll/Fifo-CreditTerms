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

namespace Webkul\WebApplicationFirewall\Model;

class FrontendTwoStepAuth extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Framework\DataObject\IdentityInterface,
    \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface
{
    /**
     * cache tag of prescription
     * @var CACHE_TAG
     */
    const CACHE_TAG = 'webkul_frontend_two_step_auth';

    /**
     * @var $_cacheTag
     */
    protected $_cacheTag = 'webkul_frontend_two_step_auth';

    /**
     * @var $_eventPrefix
     */
    protected $_eventPrefix = 'webkul_frontend_two_step_auth';

    protected function _construct()
    {
        $this->_init(\Webkul\WebApplicationFirewall\Model\ResourceModel\FrontendTwoStepAuth::class);
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getDefaultValues()
    {
        $values = [];
        return $values;
    }

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get customer email
     * @return string|null
     */
    public function getCustomerEmail()
    {
        return $this->getData(self::CUSTOMER_EMAIL);
    }

    /**
     * Set customer email
     * @param string $email
     * @return \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface
     */
    public function setCustomerEmail($email)
    {
        return $this->setData(self::CUSTOMER_EMAIL, $email);
    }

    /**
     * Get secret_key
     * @return string|null
     */
    public function getSecretKey()
    {
        return $this->getData(self::SECRET_KEY);
    }

    /**
     * Set secret_key
     * @param string $secretKey
     * @return \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface
     */
    public function setSecretKey($secretKey)
    {
        return $this->setData(self::SECRET_KEY, $secretKey);
    }

    /**
     * Get devices data
     * @return string|null
     */
    public function getDevicesData()
    {
        return $this->getData(self::DEVICES_DATA);
    }

    /**
     * Set device_token
     * @param string $devicesData
     * @return \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface
     */
    public function setDevicesData($devicesData)
    {
        return $this->setData(self::DEVICES_DATA, $devicesData);
    }

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * is auth enabled
     * @return string|null
     */
    public function getIsAuthEnabled()
    {
        return $this->getData(self::IS_AUTH_ENABLED);
    }

    /**
     * set auth enabled
     * @param string $isAuthEnabled
     * @return \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface
     */
    public function setIsAuthEnabled($isAuthEnabled)
    {
        return $this->setData(self::IS_AUTH_ENABLED, $isAuthEnabled);
    }
}
