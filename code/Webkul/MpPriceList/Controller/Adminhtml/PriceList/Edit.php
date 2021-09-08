<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPriceList
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPriceList\Controller\Adminhtml\PriceList;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Webkul\MpPriceList\Model\PriceListFactory;

class Edit extends Action
{
    /**
     * @var \Webkul\MpPriceList\Model\PriceListFactory
     */
    protected $_priceList;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param PriceListFactory $priceList
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        PriceListFactory $priceList
    ) {
        $this->_backendSession = $context->getSession();
        $this->_registry = $registry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_priceList = $priceList;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $priceList = $this->_priceList->create();
        $id = (int)$this->getRequest()->getParam('id');
        if ($id) {
            $priceList->load($this->getRequest()->getParam('id'));
            if (!$priceList->getId()) {
                $this->messageManager->addError(__('Item does not exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $data = $this->_backendSession->getFormData(true);
        if (!empty($data)) {
            $priceList->setData($data);
        }
        $this->_registry->register('mppricelist_pricelist', $priceList);
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_MpPriceList::pricelist');
        $resultPage->getConfig()->getTitle()->prepend(__('Price List'));
        $resultPage->getConfig()->getTitle()->prepend($id ? $priceList->getTitle() : __('New Price List'));
        return $resultPage;
    }
}
