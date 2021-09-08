<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerCategory
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerCategory\Block;

use Webkul\MpSellerCategory\Model\ResourceModel\Product\CollectionFactory as SellerCategoryProductCollection;

/**
 * class Category is used to provide data of Category
 */
class Category extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Webkul\MpSellerCategory\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Webkul\MpSellerCategory\Model\CategoryFactory
     */
    protected $_mpSellerCategoryFactory;

    /**
     * @var SellerCategoryProductCollection
     */
    protected $_sellerCategoryProductCollectionFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\MpSellerCategory\Helper\Data $helper
     * @param \Webkul\MpSellerCategory\Model\CategoryFactory $mpSellerCategoryFactory
     * @param SellerCategoryProductCollection $sellerCategoryProductCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\MpSellerCategory\Helper\Data $helper,
        \Webkul\MpSellerCategory\Model\CategoryFactory $mpSellerCategoryFactory,
        SellerCategoryProductCollection $sellerCategoryProductCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        array $data = []
    ) {
        $this->mpHelper = $mpHelper;
        $this->_jsonHelper = $jsonHelper;
        $this->_helper = $helper;
        $this->_storeManager = $storeManager;
        $this->_mpSellerCategoryFactory = $mpSellerCategoryFactory;
        $this->_sellerCategoryProductCollectionFactory = $sellerCategoryProductCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get Product Ids of Seller Category
     *
     * @param boolean $isJson
     *
     * @return array
     */
    public function getProductIds($isJson = false)
    {
        $productIds = [];
        $categoryId = $this->getRequest()->getParam('id');
        if ($categoryId) {
            $collection = $this->_sellerCategoryProductCollectionFactory->create();
            $collection->addFieldToFilter("seller_category_id", $categoryId);
            foreach ($collection as $item) {
                if (!in_array($item->getProductId(), $productIds)) {
                    $productIds[] = $item->getProductId();
                }
            }
        }

        if ($isJson) {
            return $this->_jsonHelper->jsonEncode($productIds);
        }

        return $productIds;
    }

    /**
     * Get Json from Array
     *
     * @param array $data
     *
     * @return string
     */
    public function getJsonFromArray($data)
    {
        return $this->_jsonHelper->jsonEncode($data);
    }

    /**
     * Undocumented function
     *
     * @param integer $categoryId
     *
     * @return array
     */
    public function getCategoryData($categoryId = 0)
    {
        $data = ["id" => "", "category_name" => "", "position" => "", "status" => 1, "product_ids" => ""];
        try {
            if (!$categoryId) {
                $categoryId = $this->getRequest()->getParam('id');
            }

            if (!$categoryId) {
                return $data;
            }

            $mpSellerCategory = $this->_mpSellerCategoryFactory->create()->load($categoryId);
            if (!empty($mpSellerCategory->getId())) {
                $data["id"] = $mpSellerCategory->getId();
                $data["category_name"] = $mpSellerCategory->getCategoryName();
                $data["position"] = $mpSellerCategory->getPosition();
                $data["status"] = $mpSellerCategory->getStatus();
                $data["product_ids"] = $mpSellerCategory->getProductIds();
            }
        } catch (\Exception $e) {
            return $data;
        }

        return $data;
    }

    /**
     * Get All Status
     *
     * @return array
     */
    public function getAllStatus()
    {
        return $this->_helper->getAllStatus();
    }

    public function getStoreUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Get Marketplace helper data
     *
     * @return object
     */
    public function getMpHelper()
    {
        return $this->mpHelper;
    }
}
