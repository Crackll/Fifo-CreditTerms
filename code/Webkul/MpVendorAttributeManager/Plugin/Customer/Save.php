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
namespace Webkul\MpVendorAttributeManager\Plugin\Customer;

class Save
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\Collection
     */
    protected $vendorAttributeCollection;

    /**
     * @var $attributeCollection
     */
    protected $attributeCollection;

    /**
     * @var $customerFactory
     */
    protected $customerFactory;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Eav\Model\Entity $eavEntity,
        \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory $vendorAttrCollection,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->_request = $request;
        $this->_eavEntity = $eavEntity;
        $this->customerFactory = $customerFactory;
        $this->vendorAttributeCollection = $vendorAttrCollection;
        $this->_attributeCollection = $attributeCollection;
    }

    public function beforeExecute(\Magento\Customer\Controller\Adminhtml\Index\Save $subject)
    {
        $customerData = $this->_request->getPostValue();
        if (isset($customerData['customer']['entity_id'])) {
            $customerId = $customerData['customer']['entity_id'];
            $customerDataKeys = array_keys($customerData['customer']);
            $customer = $this->customerFactory->create()->load($customerId);

            $customAttributes = $this->getAttributeCollection();
            if (!empty($customAttributes)) {
                foreach ($customAttributes as $attribute) {
                    if (!in_array($attribute->getAttributeCode(), $customerDataKeys)) {
                        $customerData['customer'][$attribute->getAttributeCode()] =
                            $attribute->getFrontend()->getValue($customer);
                    }
                }
            }
        }

        $this->_request->setPostValue($customerData);
    }

    /**
     * getAttributeCollection
     * @param void
     * @return object
     */
    private function getAttributeCollection()
    {
        try {
            $vendorCollection = $this->vendorAttributeCollection->create();
            $vendorAttribute = $vendorCollection->getTable('marketplace_vendor_attribute');

            $typeId = $this->_eavEntity->setType('customer')->getTypeId();
            $collection = $this->_attributeCollection->create()
               ->setEntityTypeFilter($typeId)
               ->setOrder('sort_order', 'ASC');

            $collection->getSelect()
            ->join(
                ["vendor_attr" => $vendorAttribute],
                "vendor_attr.attribute_id = main_table.attribute_id"
            );

            return $collection;
        } catch (\Exception $ex) {
            return false;
        }

        return false;
    }
}
