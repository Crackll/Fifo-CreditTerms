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

class Manage extends \Webkul\MpSellerCategory\Controller\AbstractCategory
{
    /**
     * @return \Magento\Framework\View\Result\Page
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
            $resultPage->addHandle('mpsellercategory_layout2_category_manage');
        }

        $resultPage->getConfig()->getTitle()->set(__('Manage Categories'));
        return $resultPage;
    }
}
