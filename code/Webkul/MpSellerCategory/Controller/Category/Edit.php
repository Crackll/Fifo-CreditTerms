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
namespace Webkul\MpSellerCategory\Controller\Category;

class Edit extends \Webkul\MpSellerCategory\Controller\AbstractCategory
{
    /**
     * Seller Category Edit action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        if (!$this->_marketplaceHelper->isSeller()) {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }

        $resultPage = $this->_resultPageFactory->create();
        if ($this->_marketplaceHelper->getIsSeparatePanel()) {
            $resultPage->addHandle('mpsellercategory_layout2_category_edit');
        }

        if (!empty($this->getRequest()->getParam("id"))) {
            $sellerCategory = $this->getSellerCategory();
            if (empty($sellerCategory->getId())) {
                $this->messageManager->addError("Category does not exist");
                return $this->resultRedirectFactory->create()->setPath(
                    'mpsellercategory/category/manage',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }

            $title = $sellerCategory->getCategoryName();
        } else {
            $title = "New Category";
        }

        $resultPage->getConfig()->getTitle()->set(__($title));
        return $resultPage;
    }
}
