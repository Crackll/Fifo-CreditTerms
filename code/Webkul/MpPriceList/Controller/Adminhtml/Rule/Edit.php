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
namespace Webkul\MpPriceList\Controller\Adminhtml\Rule;

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
     * @var \Webkul\MpPriceList\Model\RuleFactory
     */
    protected $_rule;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Webkul\MpPriceList\Model\RuleFactory $rule
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\MpPriceList\Model\RuleFactory $rule
    ) {
        $this->_backendSession = $context->getSession();
        $this->_registry = $registry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_rule = $rule;
        parent::__construct($context);
    }

    public function execute()
    {
        $rule = $this->_rule->create();
        if ($this->getRequest()->getParam('id')) {
            $rule->load($this->getRequest()->getParam('id'));
            if (!$rule->getId()) {
                $this->messageManager->addError(__('Item does not exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $data = $this->_backendSession->getFormData(true);
        if (!empty($data)) {
            $rule->setData($data);
        }
        
        $this->_registry->register('mppricelist_rule', $rule);

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_MpPriceList::pricelist');
        $resultPage->getConfig()->getTitle()->prepend(__('Rules'));
        $resultPage->getConfig()->getTitle()->prepend($rule->getId() ? $rule->getTitle() : __('New Rule'));
        return $resultPage;
    }
}
