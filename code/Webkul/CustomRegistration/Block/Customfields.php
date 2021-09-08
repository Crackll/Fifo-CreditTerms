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
namespace Webkul\CustomRegistration\Block;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;

class Customfields extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $_attributeCollection;

    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $_eavEntity;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_session;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Model\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Webkul\CustomRegistration\Helper\Data
     */
    protected $dataHelper;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param CustomerFactory $customer
     * @param \Magento\Customer\Model\AttributeFactory $attributeFactory
     * @param Session $session
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Webkul\CustomRegistration\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        \Magento\Eav\Model\Entity $eavEntity,
        CustomerFactory $customer,
        \Magento\Customer\Model\AttributeFactory $attributeFactory,
        Session $session,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Webkul\CustomRegistration\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_attributeCollection = $attributeCollection;
        $this->_eavEntity = $eavEntity;
        $this->_attributeFactory = $attributeFactory;
        $this->_customer = $customer;
        $this->_session = $session;
        $this->_objectManager = $objectManager;
        $this->_urlEncoder = $urlEncoder;
        $this->_urlBuilder = $urlBuilder;
        $this->dataHelper = $dataHelper;
    }

    /**
     * [attributeCollectionFilter description]
     * @return [type] [description]
     */
    public function attributeCollectionFilter()
    {
        $typeId = $this->_eavEntity->setType('customer')->getTypeId();
        $currentWebsiteId = $this->dataHelper->getCurrentWebsiteId();
        $query = 'ccp.status = 1';
        $query .= ' AND (ccp.website_ids LIKE \'%'.'"'.$currentWebsiteId.'"'.'%\' OR ccp.website_ids LIKE \'%"0"%\')';
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
            ["status" => "status","wk_frontend_input" => "wk_frontend_input"]
        )->where($query);

        return $collection;
    }

    /**
     * get current customer info.
     * @return object
     */
    public function getCurrentCustomer()
    {
        $customerId = $this->_session->getCustomer()->getId();
        $customerData = $this->_customer->create()->load($customerId);
        return $customerData;
    }

    /**
     *
     * @param  int $id
     * @return array
     */
    public function getUsedInForms($id)
    {
        $attributeModel = $this->_attributeFactory->create();
        return $attributeModel->load($id)->getUsedInForms();
    }

    /**
     *
     * @return string
     */
    public function encodeFileName($type, $filePath)
    {
        $url = $this->_urlBuilder->getUrl(
            '*/media/view',
            [$type => $this->_urlEncoder->encode(ltrim($filePath, '/'))]
        );
        return $url;
    }

    /**
     * Retrieve form data
     *
     * @return mixed
     */
    public function getFormData()
    {
        $data = $this->getData('form_data');
        if ($data === null) {
            $formData = $this->_session->getCustomerFormData(true);
            $data = new \Magento\Framework\DataObject();
            if ($formData) {
                $data->addData($formData);
                $data->setCustomerData(1);
            }
            if (isset($data['region_id'])) {
                $data['region_id'] = (int)$data['region_id'];
            }
            $this->setData('form_data', $data);
        }
        return $data;
    }
}
