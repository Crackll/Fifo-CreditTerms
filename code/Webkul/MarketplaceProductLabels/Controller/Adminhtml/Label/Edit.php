<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceProductLabels
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MarketplaceProductLabels\Controller\Adminhtml\Label;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Webkul\MarketplaceProductLabels\Model\LabelFactory $labelFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\MarketplaceProductLabels\Model\LabelFactory $labelFactory
    ) {
        $this->backendSession = $context->getSession();
        $this->resultPageFactory = $resultPageFactory;
        $this->labelFactory = $labelFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $labelModel = $this->labelFactory->create();
        $labelId = $this->getRequest()->getParam('id');
        if ($labelId) {
            $labelModel->load($this->getRequest()->getParam('id'));
            if (!$labelModel->getId()) {
                $this->messageManager->addError(__('Item does not exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        
        $data = $this->backendSession->getFormData(true);
        if (!empty($data)) {
            $labelModel->setData($data);
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_MarketplaceProductLabels::label');
        $resultPage->getConfig()->getTitle()
                    ->prepend($labelId ? __('Edit Product Label %1', $labelId): __('Add New Product Label'));
        return $resultPage;
    }
}
