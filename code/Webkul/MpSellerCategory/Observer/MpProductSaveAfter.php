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
namespace Webkul\MpSellerCategory\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\MpSellerCategory\Model\ResourceModel\Product\CollectionFactory as SellerCategoryProductCollection;

class MpProductSaveAfter implements ObserverInterface
{
    /**
     * @var \Webkul\MpSellerCategory\Helper\Data
     */
    protected $_helper;

    /**
     * @var SellerCategoryProductCollection
     */
    protected $_sellerCategoryProductCollectionFactory;

    /**
     * @param \Webkul\MpSellerCategory\Helper\Data $helper
     * @param SellerCategoryProductCollection $sellerCategoryProductCollectionFactory
     */
    public function __construct(
        \Webkul\MpSellerCategory\Helper\Data $helper,
        SellerCategoryProductCollection $sellerCategoryProductCollectionFactory
    ) {
        $this->_helper = $helper;
        $this->_sellerCategoryProductCollectionFactory = $sellerCategoryProductCollectionFactory;
    }

    /**
     * Marketplace Product Save After Observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_helper->isAllowedSellerCategories()) {
            return $this;
        }

        $errorMsg = "";
        try {
            $data = $observer->getEvent()->getData();
            if (empty($data[0]['id']) || empty($data[0]['product'])) {
                return;
            }

            $productId = $data[0]['id'];
            $productData = $data[0]['product'];
            $sellerCategoryProductCollection = $this->_sellerCategoryProductCollectionFactory->create();
            $sellerCategoryProductCollection->addFieldToFilter("product_id", $productId);
            $sellerCategoryProductCollection->removeCategories();
            if (!empty($productData['seller_category_ids'])) {
                $sellerCategoryProductCollection->assignCategories($productId, $productData['seller_category_ids']);
            }
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
        }
    }
}
