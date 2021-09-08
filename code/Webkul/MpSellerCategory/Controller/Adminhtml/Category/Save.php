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
namespace Webkul\MpSellerCategory\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpSellerCategory\Model\ResourceModel\Category\CollectionFactory as MpSellerCategoryCollection;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Webkul\MpSellerCategory\Helper\Data
     */
    protected $_helper;

    /**
     * @var MpSellerCategoryCollection
     */
    protected $_mpSellerCategoryFactory;

    /**
     * @param Context $context
     * @param \Webkul\MpSellerCategory\Helper\Data $helper
     * @param \Webkul\MpSellerCategory\Model\CategoryFactory $mpSellerCategoryFactory
     */
    public function __construct(
        Context $context,
        \Webkul\MpSellerCategory\Helper\Data $helper,
        \Webkul\MpSellerCategory\Model\CategoryFactory $mpSellerCategoryFactory
    ) {
        parent::__construct($context);
        $this->_helper = $helper;
        $this->_mpSellerCategoryFactory = $mpSellerCategoryFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            try {
                $postData = $this->getRequest()->getPostValue();
                $postData = $postData["category"];
                $validateSellerId =  true;
                $result = $this->_helper->validateData($postData, $validateSellerId);
                if ($result['error']) {
                    $this->messageManager->addError(__($result['msg']));
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }

                $sellerCategoryId = !empty($postData["entity_id"]) ? $postData["entity_id"] : 0;
                $sellerCategory = $this->getSellerCategory($sellerCategoryId);
                if ($this->_helper->isExistingCategory(
                    $postData['category_name'],
                    $postData["seller_id"],
                    $sellerCategoryId
                )) {
                    $this->messageManager->addError(__("Category '%1' already exist.", $postData['category_name']));
                    if ($sellerCategory->getId()) {
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/edit',
                            ['id' => $sellerCategory->getId(), '_secure' => $this->getRequest()->isSecure()]
                        );
                    } else {
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/new',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                }

                if ($sellerCategory->getId()) {
                    unset($postData["seller_id"]);
                    $sellerCategory->addData($postData)->setId($sellerCategory->getId());
                } else {
                    $postData["is_admin_assign"] = 1;
                    $sellerCategory->setData($postData);
                }

                $sellerCategory->save();
                $id = $sellerCategory->getId();
                $this->messageManager->addSuccess(__("Category saved successfully."));
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/edit',
                    ['id' => $id, '_secure' => $this->getRequest()->isSecure()]
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * Get Category
     *
     * @param integer $sellerCategoryId
     *
     * @return \Webkul\MpSellerCategory\Model\Category
     */
    public function getSellerCategory($sellerCategoryId = 0)
    {
        $sellerCategory = $this->_mpSellerCategoryFactory->create();
        if (!$sellerCategoryId) {
            $sellerCategoryId = (int) $this->getRequest()->getParam("id");
        }

        $sellerCategory->load($sellerCategoryId);
        return $sellerCategory;
    }

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpSellerCategory::category');
    }
}
