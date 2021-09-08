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


namespace Webkul\MpSellerBuyerCommunication\Block\Adminhtml;

use Webkul\MpSellerBuyerCommunication\Model\Source\QueryStatus;
use Webkul\MpSellerBuyerCommunication\Model\ResourceModel\SellerBuyerCommunication\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class Reply extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\MpSellerBuyerCommunication\Model\ResourceModel\SellerBuyerCommunication\CollectionFactory
     */
    protected $_sellerCommCollectionFactory;

    /**
     * @var \Webkul\MpSellerBuyerCommunication\Model\Source\QueryStatus
     */
    protected $_status;

    /**
     * @param Magento\Catalog\Block\Product\Context              $context
     * @param Magento\Framework\Filesystem                       filesystem
     * @param CollectionFactory                                  $sellerCommCollectionFactory
     * @param QueryStatus                                        $queryStatus
     * @param array                                              $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        CollectionFactory $sellerCommCollectionFactory,
        QueryStatus $queryStatus,
        JsonHelper $json,
        \Magento\Backend\Block\Widget\Container $container,
        array $data = []
    ) {
        $this->_sellerCommCollectionFactory = $sellerCommCollectionFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        $this->_status = $queryStatus;
        $this->json = $json;
        parent::__construct($context, $data);
    }

    /**
     * get query status html element
     * @return string
     */
    public function getQueryStatus()
    {
        $status_arr = $this->_status->toOptionArray();
        $comm_data = $this->getSellerBuyerCommunicationById($this->getRequest()->getParam('comm_id'));

        $arr = [];
        $optionHtml = '';
        foreach ($status_arr as $status) {
            $selected = '';
            if ($status['value'] == $comm_data['query_status']) {
                $selected = 'selected';
            }
            $optionHtml .= '<option value="'.$status['value'].'" '.$selected.'>'.$status['label'].'</option>';
            $arr[$status['value']] = $status['label'];
        }
        return $optionHtml;
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
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        return $this;
    }

    /**
     * @return bool|\Webkul\MpSellerBuyerCommunication\Model\SellerBuyerCommunication
     */
    public function getSellerBuyerCommunicationById()
    {
        $id = $this->getRequest()->getParam('comm_id');
        $collection = $this->_sellerCommCollectionFactory->create()
        ->addFieldToFilter(
            'seller_id',
            0
        )->addFieldToFilter(
            'entity_id',
            $id
        );
        $data = [];
        if ($collection->getSize()) {
            foreach ($collection as $value) {
                $data['subject'] = $value['subject'];
                $data['product_id'] = $value['product_id'];
                $data['query_status'] = $value['query_status'];
                $data['support_type'] = $value['support_type'];
                $data['product_name'] = $value['product_name'];
            }
        }
        return $data;
    }

    /**
     * Get Request Id
     */
    public function getRequestId()
    {
        return $this->getRequest()->getParam('comm_id');
    }

    /**
     * Get Json Data
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * Check permission for passed action
     *
     * @param  string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
