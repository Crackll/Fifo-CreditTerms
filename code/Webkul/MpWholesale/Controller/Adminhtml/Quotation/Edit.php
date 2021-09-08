<?php
/**
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Controller\Adminhtml\Quotation;

use Magento\Backend\App\Action;
use Webkul\MpWholesale\Model\QuotesFactory;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Webkul\Quotesystem\Model\QuotesFactory
     */
    protected $quotesFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param QuotesFactory $quotesFactory
     * @param Magento\Backend\Model\Session $adminSession
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        QuotesFactory $quotesFactory,
        \Magento\Backend\Model\Session $adminSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        $this->quotesFactory = $quotesFactory;
        $this->adminSession = $adminSession;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Webkul_MpWholesale::quotation'
        );
    }
    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_MpWholesale::menu')
            ->addBreadcrumb(__('Manage Quotes'), __('Manage Quotes'));
        return $resultPage;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->quotesFactory->create();
        if ($id) {
            $model->load($id);
            if (!$model->getEntityId()) {
                $this->messageManager->addError(
                    __('This quote no longer exists.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $data = $this->adminSession->getFormData(true);
        if (!empty($data) || $id) {
            $model->setData($data);
            $this->coreRegistry->register('quote_data', $model);
            $resultPage = $this->_initAction();
            $resultPage->addBreadcrumb(__('View Quote'), __('View Quote'));
            $resultPage->getConfig()->getTitle()->prepend(__('View Quote'));
            $resultPage->addContent(
                $resultPage->getLayout()->createBlock(
                    \Webkul\MpWholesale\Block\Adminhtml\Quotes\Edit::class
                )
            );
            $resultPage->addLeft(
                $resultPage->getLayout()->createBlock(
                    \Webkul\MpWholesale\Block\Adminhtml\Quotes\Edit\Tabs::class
                )
            );
            return $resultPage;
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index');
        }
    }
}
