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

class Save extends \Webkul\MpSellerCategory\Controller\AbstractCategory
{
    /**
     * Seller Category Save action
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath(
                        'mpsellercategory/category/manage',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }

                $postData = $this->getRequest()->getPostValue();
                $postData['seller_id'] = $this->getSellerId();
                $result = $this->_helper->validateData($postData);
                if ($result['error']) {
                    $this->messageManager->addError(__($result['msg']));
                    return $this->resultRedirectFactory->create()->setPath(
                        'mpsellercategory/category/manage',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }

                $sellerCategory = $this->getSellerCategory();
                if ($this->_helper->isExistingCategory($postData['category_name'])) {
                    $this->messageManager->addError(__("Category '%1' already exist.", $postData['category_name']));
                    if ($sellerCategory->getId()) {
                        return $this->resultRedirectFactory->create()->setPath(
                            'mpsellercategory/category/edit',
                            ['id' => $sellerCategory->getId(), '_secure' => $this->getRequest()->isSecure()]
                        );
                    }

                    return $this->resultRedirectFactory->create()->setPath(
                        'mpsellercategory/category/new',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }

                if ($sellerCategory->getId()) {
                    $sellerCategory->addData($postData)->setId($sellerCategory->getId());
                } else {
                    $sellerCategory->setData($postData);
                }

                $sellerCategory->save();
                $id = $sellerCategory->getId();
                $this->messageManager->addSuccess(__("Category saved successfully."));
                $this->_helper->clearCache();
                return $this->resultRedirectFactory->create()->setPath(
                    'mpsellercategory/category/edit',
                    ['id' => $id, '_secure' => $this->getRequest()->isSecure()]
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath(
                    'mpsellercategory/category/manage',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'mpsellercategory/category/manage',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
