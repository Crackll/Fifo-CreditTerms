<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomRegistration
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\CustomRegistration\Plugin\Controller;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Controller\Result\RedirectFactory;

class CreatePost extends \Magento\Customer\Controller\Account\CreatePost
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var RedirectFactory
     */
    protected $_redirect;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $_attributeCollection;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    protected $_entityAttribute;

    /**
     * @var \Webkul\CustomRegistration\Model\CustomfieldsFactory
     */
    protected $customFieldsFactory;

    /**
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Magento\Eav\Model\Entity\Attribute $entityAttribute
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param RedirectFactory $redirect
     * @param \Webkul\CustomRegistration\Model\CustomfieldsFactory $customfieldsFactory
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        RedirectFactory $redirect,
        \Webkul\CustomRegistration\Model\CustomfieldsFactory $customfieldsFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->_eavEntity = $eavEntity;
        $this->_entityAttribute = $entityAttribute;
        $this->_redirect = $redirect;
        $this->_attributeCollection = $attributeCollection;
        $this->customFieldsFactory = $customfieldsFactory;
    }

    public function aroundExecute(
        $subject,
        $proceed,
        $data = "null",
        $requestInfo = false
    ) {
        // Checking parameters..
        $customDatas = $this->_request->getParams();
        if(isset($customDatas['country_id'])){
            return $proceed();
        }
        $resultRedirect = $subject->resultRedirectFactory->create();

        $refererUrl = explode('?', $subject->_redirect->getRefererUrl())[0];

        $typeId = $this->_eavEntity->setType('customer')->getTypeId();

        $collection = $this->_attributeCollection->create()
            ->setEntityTypeFilter($typeId)
            ->addFilter('is_user_defined', 1)
            ->setOrder('sort_order', 'ASC');

        $error = [];
        $customData = $this->_request->getParams();
        $filesData =  $this->_request->getFiles()->toArray();
        $wholeData = array_merge($customData, $filesData);

        $currentWebsiteId = $this->_storeManager->getWebsite()->getId();
        $customFieldsCollection = $this->customFieldsFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('has_parent', 0)
            ->addFieldToFilter('website_ids', ['like' => '%"'.$currentWebsiteId.'"%']);
        foreach ($customFieldsCollection as $customField) {
            $attribute = $this->getAttributeInfo('customer', $customField->getAttributeCode());
            if ($attribute && $attribute->getId()) {
                $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
                $_attributeFactory = $objectManager->create('Magento\Customer\Model\AttributeFactory');
                $attributeModel = $_attributeFactory->create();
                $usedInForms =  $attributeModel->load($attribute->getId())->getUsedInForms();
                $required = explode(' ', $attribute->getFrontendClass());
                if (in_array('required', $required) && in_array('customer_account_create',$usedInForms)) {
                    if (empty($wholeData[$attribute->getAttributeCode()])) {
                        $error[] = $attribute->getAttributeCode();
                    }
                }
            }
        }

        foreach ($collection as $attribute) {
            foreach ($customData as $attributeCode => $attributeValue) {
                if ($attributeCode==$attribute->getAttributeCode()) {
                    $required = explode(' ', $attribute->getFrontendClass());

                    if (in_array('required', $required)) {
                        if (empty($attributeValue)) {
                            $error[] = $attribute->getAttributeCode();
                        }
                    }
                }
            }
        }
        if (!empty($error)) {
            $subject->messageManager->addError(
                __(
                    'Please Fill all the Required Fields.'
                )
            );
            $resultRedirect = $this->_redirect->create();
            $resultRedirect->setPath('customer/account/create');
            return $resultRedirect;
        }

        if ($this->getConfigData('enable_registration')) {
            $params = $this->_request->getParams();
            if (!isset($params['account_create_privacy_condition']) ||
                $params['account_create_privacy_condition'] == 0
            ) {
                $subject->messageManager->addError(__('Check Terms and Conditions & Privacy & Cookie Policy.'));
                $resultRedirect = $this->_redirect->create();
                $resultRedirect->setPath('*/*/create', ['_secure' => true]);
                return $resultRedirect;
            }
        }
        return $proceed();
    }

    /**
     * Get attribute info by attribute code and entity type
     *
     * @param mixed $entityType can be integer, string, or instance of class Mage\Eav\Model\Entity\Type
     * @param string $attributeCode
     * @return \Magento\Eav\Model\Entity\Attribute
     */
    public function getAttributeInfo($entityType, $attributeCode)
    {
        return $this->_entityAttribute->loadByCode($entityType, $attributeCode);
    }

    /**
     * Retrieve information from carrier configuration.
     *
     * @param string $field
     *
     * @return void|false|string
     */
    public function getConfigData($field)
    {
        $path = 'customer_termandcondition/parameter/'.$field;
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        );
    }
}
