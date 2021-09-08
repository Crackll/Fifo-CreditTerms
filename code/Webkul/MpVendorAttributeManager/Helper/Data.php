<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorAttributeManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpVendorAttributeManager\Helper;

use Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory as VendorAttributeCollection;

/**
 * MpVendorAttributeManager data helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var string
     */
    protected $_code = 'vendor_attribute';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Core store config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $_eavEntity;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $_attributeCollection;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory
     */
    protected $_vendorAttributeCollection;

    /**
     * @var \Magento\Eav\Model\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory
     */
    protected $_vendorAssignGroupFactory;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorGroupFactory
     */
    protected $_vendorGroupFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     * @param \Magento\Framework\App\RequestInterface $request
     * @param VendorAttributeCollection $vendorAttributeCollection
     * @param \Magento\Eav\Model\AttributeFactory $attributeFactory
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory $vendorAssignGroupFactory
     * @param \Webkul\MpVendorAttributeManager\Model\VendorGroupFactory $vendorGroupFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        \Magento\Framework\App\RequestInterface $request,
        VendorAttributeCollection $vendorAttributeCollection,
        \Magento\Eav\Model\AttributeFactory $attributeFactory,
        \Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory $vendorAssignGroupFactory,
        \Webkul\MpVendorAttributeManager\Model\VendorGroupFactory $vendorGroupFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Url\EncoderInterface $urlEncoder
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_eavEntity = $eavEntity;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->request = $request;
        $this->_attributeCollection = $attributeCollection;
        $this->_vendorAttributeCollection = $vendorAttributeCollection;
        $this->_attributeFactory = $attributeFactory;
        $this->_vendorAssignGroupFactory = $vendorAssignGroupFactory;
        $this->_vendorGroupFactory = $vendorGroupFactory;
        $this->_urlBuilder = $urlBuilder;
        $this->_urlEncoder = $urlEncoder;
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
        if (empty($this->_code)) {
            return false;
        }
        $path = 'marketplace/'.$this->_code.'/'.$field;

        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()
        );
    }
    /**
     * check oi vendor registarion form.
     *
     * @return bool
     */
    public function isVendorRegistration()
    {
        $vendorRegister = $this->request->getParam('v');
        if ($vendorRegister) {
            return true;
        }

        return false;
    }

    /**
     * Get Attributes Collection
     *
     * @param  boolean $isSeller
     *
     * @return Collection $attributeCollection
     */
    public function getAttributeCollection($isSeller = false, $checkforProfile = false)
    {
        $attributeUsed = [0,1];
        if ($isSeller) {
            $attributeUsed = [0,2];
        }

        $customAttributes = $this->_vendorAttributeCollection->create()
                                ->getVendorAttributeCollection()
                                ->addFieldToFilter("attribute_used_for", ["in" => $attributeUsed])
                                ->addFieldToFilter("wk_attribute_status", "1");
        if ($checkforProfile) {
            $customAttributes->addFieldToFilter("show_in_front", "1");
        }

        if ($customAttributes->getSize()) {
            return $customAttributes;
        }

        return false;
    }

    public function getAttributesByGroupId($groupId)
    {
        $groupAttributes = $this->_vendorAssignGroupFactory->create()->getCollection()
                                ->getGroupAttributes($groupId);
        return $groupAttributes;
    }

    /**
     * get attributes groups
     * @return array
     */
    public function getAttributeGroup()
    {
        $groups = [];

        $vendorGroupCollection = $this->_vendorGroupFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('status', ['eq' => 1]);

        if ($vendorGroupCollection->getSize()) {
            $groups = $vendorGroupCollection->getColumnValues('group_name');
        }

        return $groups;
    }
    /**
     * Get current store
     * @return object
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Provide config to window.vendorConfig
     * @return array
     */
    public function getVendorConfig()
    {
        $config['groups'] = $this->getAttributeGroups();
        $config['groups_attribute'] = $this->getAttributeCollectionForGroups($config['groups']);
        $config['is_attribute_assigned_to_any_customer'] = $this->getAnyAttributeAssignedToCustomer();
        $config['is_attribute_assigned_to_any_seller'] = $this->getAnyAttributeAssignedToSeller();
        return $config;
    }

    public function getAnyAttributeAssignedToCustomer()
    {
        $vendorGroupFactory = $this->_vendorGroupFactory->create();
        return $vendorGroupFactory->getAnyAttributeAssignedToCustomer();
    }

    public function getAnyAttributeAssignedToSeller()
    {
        $vendorGroupFactory = $this->_vendorGroupFactory->create();
        return $vendorGroupFactory->getAnyAttributeAssignedToSeller();
    }

    /**
     * fetch attributes by attribute group
     * @param  boolean $isSeller
     * @return array
     */
    public function getAttributeCollectionForGroups($groups)
    {
        $groupAttributes = [];
        $fileTypes = ["image","file"];
        $extensions = [];
        foreach ($fileTypes as $fileType) {
            $extensions[$fileType] = $this->getConfigData('allowede_'.$fileType.'_extension');
        }
        foreach ($groups as $group) {
            $groupAttribute = $this->getAttributesByGroupId($group['group_id']);

            foreach ($groupAttribute as $attribute) {
                $attributeClass = explode(" ", $attribute->getFrontendClass());
                $optiondata = [];

                if ($attribute->getFrontendInput() == 'select' || $attribute->getFrontendInput() == 'multiselect') {
                    $optiondata = $attribute->getSource()->getAllOptions();
                }
                $allowedExtensions = "";

                if (in_array($attribute->getFrontendInput(), $fileTypes)) {
                    $allowedExtensions = $extensions[$attribute->getFrontendInput()];
                }

                $groupAttributes['groups'][$group['group_id']][] = [
                    'attribute_id' => $attribute->getId(),
                    'attribute_code' => $attribute->getAttributeCode(),
                    'frontend_input' => $attribute->getFrontendInput(),
                    'is_required' => $attribute->getRequiredField(),
                    'label' => $attribute->getStoreLabel(),
                    'wysiwyg_enabled' => in_array('wysiwyg_enabled', $attributeClass)?1:0,
                    'option_data' => $optiondata,
                    'frontend_class' => $attribute->getFrontendClass(),
                    'extension' => $allowedExtensions,
                    'sort_order' => $attribute->getSortOrder(),
                    'used_for' => $attribute->getAttributeUsedFor()
                ];
            }
        }
        return $groupAttributes;
    }

    public function getAttributeGroups()
    {
        $groups = [];
        $groupCollection = $this->_vendorGroupFactory->create()
                                ->getCollection()
                                ->addFieldToFilter('status', ['eq' => 1]);
        if ($groupCollection->getSize()) {
            foreach ($groupCollection as $value) {
                $groups[] = [
                    'group_id' => $value->getEntityId(),
                    'group_name' => $value->getGroupName()
                ];
            }
        }
        return $groups;
    }

    public function getAllowedImageExtensions()
    {
        $allowedImageExtensions = $this->scopeConfig->getValue(
            'marketplace/vendor_attribute/allowede_image_extension',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $allowedImageExtensions;
    }

    public function getAllowedFileExtensions()
    {
        $allowedFileExtensions = $this->scopeConfig->getValue(
            'marketplace/vendor_attribute/allowede_file_extension',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $allowedFileExtensions;
    }

    public function getFieldFrontendInput($attributeCode)
    {
        $attributeCollection = $this->_attributeFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter('attribute_code', $attributeCode);

        if (count($attributeCollection) == 1) {
            foreach ($attributeCollection as $attribute) {
                return $attribute->getFrontendInput();
            }
        }
    }

    public function getTermConditionConfig($field)
    {
        if (empty($this->_code)) {
            return false;
        }
        $path = 'marketplace/termcondition/'.$field;

        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()
        );
    }

    public function encodeFileName($type, $filePath)
    {
        $url = $this->_urlBuilder->getUrl(
            'vendorattribute/preview/fileview',
            [$type => $this->_urlEncoder->encode(ltrim($filePath, '/'))]
        );
        return $url;
    }

    public function isB2BMarketplaceInstalled()
    {
        if ($this->_moduleManager->isEnabled('Webkul_B2BMarketplace')) {
            return true;
        }
        return false;
    }
}
