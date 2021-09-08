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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

class Edit extends Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Webkul\MpSellerCategory\Model\CategoryFactory
     */
    protected $_mpSellerCategory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Webkul\MpSellerCategory\Model\CategoryFactory $mpSellerCategory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\MpSellerCategory\Model\CategoryFactory $mpSellerCategory
    ) {
        $this->_backendSession = $context->getSession();
        $this->_registry = $registry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_mpSellerCategory = $mpSellerCategory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $mpSellerCategory = $this->_mpSellerCategory->create();
        $id = (int)$this->getRequest()->getParam('id');
        if ($id) {
            $mpSellerCategory->load($this->getRequest()->getParam('id'));
            if (!$mpSellerCategory->getId()) {
                $this->messageManager->addError(__('Category does not exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $data = $this->_backendSession->getFormData(true);
        if (!empty($data)) {
            $mpSellerCategory->setData($data);
        }
        $this->_registry->register('mpsellercategory_category', $mpSellerCategory);
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_MpSellerCategory::menu');
        $resultPage->getConfig()->getTitle()->prepend($id ? $mpSellerCategory->getCategoryName() : __('New Category'));
        return $resultPage;
    }
}
