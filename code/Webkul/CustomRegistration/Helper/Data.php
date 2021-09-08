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
namespace Webkul\CustomRegistration\Helper;

use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Custom Registration Orders helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    protected $_attributeCollection;

    protected $_eavEntity;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Magento\Customer\Model\Session $customerSession
     * @param JsonHelper $jsonHelper
     * @param \Webkul\CustomRegistration\Model\CustomfieldsFactory $customfield
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Customer\Model\Session $customerSession,
        JsonHelper $jsonHelper,
        \Webkul\CustomRegistration\Model\CustomfieldsFactory $customfield
    ) {
        $this->_storeManager = $storeManager;
        $this->_attributeCollection = $attributeCollection;
        $this->_eavEntity = $eavEntity;
        $this->customerSession = $customerSession;
        $this->jsonHelper = $jsonHelper;
        $this->customfield = $customfield;
        parent::__construct($context);
    }

    /**
     * This function will decode the array to json format
     *
     * @param array $data
     * @return json
     */
    public function jsonDecodeData($data)
    {
        return $this->jsonHelper->jsonDecode($data);
    }

    /**
     * This function will return json encoded data
     *
     * @param json $data
     * @return Array
     */
    public function jsonEncodeData($data)
    {
        return $this->jsonHelper->jsonEncode($data);
    }

    /**
     * This function will return the id of the current store
     *
     * @return Integer
     */
    public function getCurrentWebsiteId()
    {
        return $this->_storeManager->getWebsite()->getId();
    }

    /**
     * This function will return the id of the current store
     *
     * @return Integer
     */
    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Get Child attribute customfield table data from custom field id
     *
     * @param int $customfieldId
     * @return array
     */
    public function getChildData($customfieldId)
    {
        $childData = [];
        $collection = $this->customfield->create()
            ->getCollection()
            ->addFieldToFilter('has_parent', $customfieldId);
        foreach ($collection as $model) {
            $childData = $model->getData();
        }
        return $childData;
    }
}
