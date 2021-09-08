<?php
/**
 * Webkul Software
 *
 * @category    Webkul
 * @package     Webkul_MpSellerBuyerCommunication
 * @author      Webkul
 * @copyright   Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */


namespace Webkul\MpSellerBuyerCommunication\Block\Adminhtml\Query;

use Magento\Customer\Model\Customer;
use Magento\Framework\App\Filesystem\DirectoryList;
use \Webkul\MpSellerBuyerCommunication\Model\ResourceModel\SellerBuyerCommunication\CollectionFactory;
use \Webkul\MpSellerBuyerCommunication\Model\ResourceModel\Conversation\CollectionFactory as ConversationFactory;
use Webkul\MpSellerBuyerCommunication\Helper\Data as HelperData;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class Conversation extends \Magento\Framework\View\Element\Template
{
    protected $sellerBuyerCommunicationLists;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CollectionFactory $sellerCommCollectionFactory,
        ConversationFactory $conversationCollectionFactory,
        HelperData $helperData,
        JsonHelper $json,
        array $data = []
    ) {
        $this->customer = $customer;
        $this->_customerSession = $customerSession;
        $this->_productRepository = $productRepository;
        $this->_sellerCommCollectionFactory = $sellerCommCollectionFactory;
        $this->_conversationCollectionFactory = $conversationCollectionFactory;
        $this->helperData = $helperData;
        $this->json = $json;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        parent::__construct($context, $data);
    }

     /**
      * get customer id
      * @return int
      */
    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }

    /**
     * get customer name
     * @param  int $customerId
     * @return string
     */
    public function getCustomerNameId($customerId)
    {
        return $this->customer->load($customerId)->getName();
    }

    /**
     * get product url
     * @param  int $productId
     * @return string
     */
    public function getProductUrlById($productId)
    {
        try {
            $product = $this->_productRepository->getById($productId);
            return $product->getProductUrl();
        } catch (\Exception $e) {
            return '#';
        }
    }

    /**
     * get Product name
     * @param  int $productId
     * @return string
     */
    public function getProductNameById($productId)
    {
        try {
            $product = $this->_productRepository->getById($productId);
            return $product->getName();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return bool|\Webkul\MpSellerBuyerCommunication\Model\ResourceModel\SellerBuyerCommunication\Collection
     */
    public function getAllCommunicationData()
    {
        if (!$this->sellerBuyerCommunicationLists) {
            $id = $this->getRequest()->getParam("comm_id");
            $collection = $this->_conversationCollectionFactory->create()
            ->addFieldToFilter(
                'comm_id',
                $id
            )->setOrder(
                'created_at',
                'desc'
            );
            $this->sellerBuyerCommunicationLists = $collection;
        }

        return $this->sellerBuyerCommunicationLists;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllCommunicationData()) {
            $this->getAllCommunicationData()->load();
        }
        return $this;
    }

    /**
     * get current url
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl(); // Give the current url of recently viewed page
    }

    /**
     * get complete imge url
     * @param  string $imageName
     * @return string
     */
    public function getImageUrl($imageName, $queryId, $commentId)
    {
        return $this->_storeManager
            ->getStore()
            ->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ).'sellerbuyercommunication/'.$queryId.'/'.$commentId.'/'.$imageName;
    }

    /**
     * get complete imge url
     * @param  string $imageName
     * @return string
     */
    public function getImageMediaPath($imageName, $queryId, $commentId)
    {
        return $this->mediaDirectory->getAbsolutePath(
            'sellerbuyercommunication/'.$queryId.'/'.$commentId.'/'.$imageName
        );
    }

    /**
     * check is image or link
     * @param string $imageName
     * @param int $queryId
     * @param int $commandId
     */
    public function isImage($imageName, $queryId, $commentId)
    {
        $url = $this->getImageMediaPath($imageName, $queryId, $commentId);
        $imageCheck = !empty($url)?getimagesize($url):false;
        if (is_array($imageCheck) && $imageCheck!==false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Mp helper in view
     */
    public function getMpHelperSellerView()
    {
        return $this->mpHelperData;
    }

    /**
     * Get Helper in view
     */
    public function getHelperSellerView()
    {
        return $this->helperData;
    }

    /**
     * Get Request Id
     */
    public function getRequestId()
    {
        return $this->getRequest()->getParam("id");
    }

    /**
     * Get Json Data
     */
    public function getJson()
    {
        return $this->json;
    }
}
