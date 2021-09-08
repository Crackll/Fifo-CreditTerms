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

use Magento\Framework\UrlInterface;

/**
 * Custom Registration Orders helper.
 */
class Order extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * url builder.
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    protected $_attributeCollection;

    protected $_eavEntity;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $registry;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->_attributeCollection = $attributeCollection;
        $this->_eavEntity = $eavEntity;
        $this->_objectManager = $objectManager;
        $this->customerSession = $customerSession;
        $this->_urlEncoder = $urlEncoder;
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($context);
    }
    /**
     * get All custom attribute collection.
     * @return collection
     */
    public function attributeCollectionFilter($websiteId = null, $forOrder = false, $forEmail = false)
    {
        $typeId = $this->_eavEntity->setType('customer')->getTypeId();
        $query = 'ccp.status = 1';
        if ($websiteId) {
            $query .= ' AND (ccp.website_ids LIKE \'%'.'"'.$websiteId.'"'.'%\' OR ccp.website_ids LIKE \'%"0"%\')';
        }
        if ($forOrder) {
            $query .= ' AND ccp.show_in_order = 1';
        }
        if ($forEmail) {
            $query .= ' AND ccp.show_in_email = 1';
        }
        $customField = $this->_objectManager->create(
            \Webkul\CustomRegistration\Model\ResourceModel\Customfields\Collection::class
        )->getTable('wk_customfields');
        $collection = $this->_attributeCollection->create()
                ->setEntityTypeFilter($typeId)
                ->setOrder('sort_order', 'ASC');
                
        $collection->getSelect()
        ->join(
            ["ccp" => $customField],
            "ccp.attribute_id = main_table.attribute_id",
            ["status" => "status"]
        )->where($query);

        return $collection;
    }
    /**
     * check for custom attribute should be display in order view
     * @param  int  $attrId
     * @return boolean
     */
    public function isShowInOrder($attrId)
    {
        $isShow = 0;
        $collection = $this->_objectManager->create(\Webkul\CustomRegistration\Model\Customfields::class)
                ->getCollection()
                ->addFieldToFilter('attribute_id', ['eq'=>$attrId])
                ->addFieldToFilter('show_in_order', ['eq'=>'1']);
        if (!empty($collection) && $collection->getSize() > 0) {
            $isShow = 1;
        }
        return $isShow;
    }
    /**
     * check for custom attribute should be display in order email
     * @param  int  $attrId
     * @return boolean
     */
    public function isShowInEmail($attrId)
    {
        $isShow = 0;
        $collection = $this->_objectManager->create(\Webkul\CustomRegistration\Model\Customfields::class)
                ->getCollection()
                ->addFieldToFilter('attribute_id', ['eq'=>$attrId])
                ->addFieldToFilter('show_in_email', ['eq'=>'1']);
        if (!empty($collection) && $collection->getSize() > 0) {
            $isShow = 1;
        }
        return $isShow;
    }
    /**
     * get current customer data.
     * @param  int $customerId
     */
    public function getCurrentCustomer($customerId)
    {
        $customerData = $this->_objectManager->create(\Magento\Customer\Model\Customer::class)->load($customerId);
        return $customerData;
    }
    /**
     * Retrieve order model.
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('sales_order');
    }

    public function encodeFileName($type, $filePath)
    {
        $url = $this->_urlBuilder->getUrl(
            'customregistration/media/view',
            [$type => $this->_urlEncoder->encode(ltrim($filePath, '/'))]
        );
        return $url;
    }
}
