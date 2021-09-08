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

use Webkul\MpSellerCategory\Model\ResourceModel\Category\CollectionFactory as SellerCategoryCollection;
use Webkul\MpSellerCategory\Model\ResourceModel\Product\CollectionFactory as SellerCategoryProductCollection;

class Product extends \Magento\Framework\View\Element\Template
{
    /**
     * @var SellerCategoryCollection
     */
    protected $_sellerCategoryCollectionFactory;

    /**
     * @var SellerCategoryProductCollection
     */
    protected $_sellerCategoryProductCollectionFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param SellerCategoryCollection $sellerCategoryCollectionFactory
     * @param SellerCategoryProductCollection $sellerCategoryProductCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        SellerCategoryCollection $sellerCategoryCollectionFactory,
        SellerCategoryProductCollection $sellerCategoryProductCollectionFactory,
        array $data = []
    ) {
        $this->_sellerCategoryCollectionFactory = $sellerCategoryCollectionFactory;
        $this->_sellerCategoryProductCollectionFactory = $sellerCategoryProductCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get Category Collection
     *
     * @return \Webkul\MpSellerCategory\Model\ResourceModel\Category\Collection
     */
    public function getAllCategories()
    {
        return $this->_sellerCategoryCollectionFactory->create();
    }

    /**
     * Get Active Categories Collection
     *
     * @return \Webkul\MpSellerCategory\Model\ResourceModel\Category\Collection
     */
    public function getCategories()
    {
        $collection = $this->getAllCategories();
        $collection->addFieldToFilter("status", 1);
        $collection->getSelect()->order("position", "ASC");
        return $collection;
    }

    /**
     * Get Product Category Ids
     *
     * @return array
     */
    public function getSelectedCategoryIds()
    {
        $categoryIds = [];
        $productId = $this->getRequest()->getParam('id');
        if ($productId) {
            $collection = $this->_sellerCategoryProductCollectionFactory->create();
            $collection->addFieldToFilter("product_id", $productId);
            foreach ($collection as $item) {
                if (!in_array($item->getSellerCategoryId(), $categoryIds)) {
                    $categoryIds[] = $item->getSellerCategoryId();
                }
            }
        }

        return $categoryIds;
    }
}
