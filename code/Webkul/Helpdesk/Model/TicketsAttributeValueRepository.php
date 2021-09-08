<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Filesystem\Driver\File;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class TicketsAttributeValueRepository implements \Webkul\Helpdesk\Api\TicketsAttributeValueRepositoryInterface
{
    /**
     * @var \Webkul\Helpdesk\Model\TicketsCustomAttributesFactory
     */
    protected $_ticketsCustomAttributesFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected $_eavAttribute;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected $_ticketsAttributeValueFactory;

    /**
     * @var \Webkul\Helpdesk\Helper\Data
     */
    protected $_helper;

    /**
     * @var File
     */
    protected $_file;

    /**
     * File Uploader factory.
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * TicketsAttributeValueRepository constructor.
     * @param \Webkul\Helpdesk\Model\TicketsCustomAttributesFactory
     * @param File $file
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Webkul\Helpdesk\Model\TicketsCustomAttributesFactory $ticketsCustomAttributesFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute,
        \Webkul\Helpdesk\Model\TicketsAttributeValueFactory $ticketsAttributeValueFactory,
        \Webkul\Helpdesk\Helper\Data $helper,
        File $file,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger
    ) {
        $this->_ticketsCustomAttributesFactory = $ticketsCustomAttributesFactory;
        $this->_eavAttribute = $eavAttribute;
        $this->_ticketsAttributeValueFactory = $ticketsAttributeValueFactory;
        $this->_helper = $helper;
        $this->_file = $file;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_helpdeskLogger = $helpdeskLogger;
    }

    /**
     * saveTicketAttributeValues
     *
     * @param int $ticketId
     * @param mixed $wholedata
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function saveTicketAttributeValues($ticketId, $wholedata)
    {
        if (isset($wholedata['type'])) {
            $attributeIds = $this->_ticketsCustomAttributesFactory->create()
            ->getAllowedTicketCustomerAttributes($wholedata['type']);
        }
        if (isset($attributeIds)) {
            foreach ($attributeIds as $attributeId) {
                $attribute = $this->_eavAttribute->load($attributeId);
                $ticketAttributeModel = $this->_ticketsAttributeValueFactory->create();
                if (isset($wholedata[$attribute['attribute_code']]) && $wholedata[$attribute['attribute_code']] != "") {
                    if ($attribute['frontend_input'] == 'multiselect') {
                        $ticketAttributeModel->setValue(implode(",", $wholedata[$attribute['attribute_code']]));
                    } else {
                        $ticketAttributeModel->setValue($wholedata[$attribute['attribute_code']]);
                    }
                }

                if ($attribute['frontend_input'] == 'image' || $attribute['frontend_input'] == 'file') {
                    $uploader = $this->_fileUploaderFactory->create(['fileId' => $attribute['attribute_code']]);
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'doc', 'pdf']);
                    $mediaPath = $this->_helper->getMediaPath();
                    $path = $mediaPath."/helpdesk/attributeattachment/".$ticketId."/";
                    $this->_file->createDirectory($path);
                    $uploadedFile = $uploader->save($path);
                    if (isset($uploadedFile['file'])) {
                        $ticketAttributeModel->setValue($uploadedFile['file']);
                    }
                }
                $ticketAttributeModel->setTicketId($ticketId);
                $ticketAttributeModel->setAttributeId($attributeId);
                $ticketAttributeModel->save();
            }
        }
    }

    /**
     * editTicketAttributeValues save ticket custom attribute values
     * @param Array $wholedata Post request data
     * @return [type]            [description]
     */
    public function editTicketAttributeValues($wholedata)
    {
        foreach ($wholedata['custom'] as $key => $attributeValue) {
            $attributeId = $this->_eavAttribute->getIdByCode("ticketsystem_ticket", $key);
            $attribute = $this->_eavAttribute->load($attributeId);
            $customAttributeValueCollection = $this->_ticketsAttributeValueFactory->create()->getCollection()
                                                ->addFieldToFilter("attribute_id", ["eq"=>$attributeId])
                                                ->addFieldToFilter("ticket_id", ["eq"=>$wholedata['ticket_id']]);

            foreach ($customAttributeValueCollection as $customAttribuevalue) {
                if ($attribute['frontend_input'] == 'multiselect') {
                    $customAttribuevalue->setValue(implode(",", $attributeValue));
                } else {
                    $customAttribuevalue->setValue($attributeValue);
                }
                $customAttribuevalue->save();
            }
        }
    }
}
