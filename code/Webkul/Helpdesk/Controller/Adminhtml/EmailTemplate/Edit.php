<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\EmailTemplate;

use Magento\Backend\App\Action;

class Edit extends Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Webkul\Helpdesk\Model\Tickets $ticketsModel
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Webkul\Helpdesk\Model\EmailTemplateFactory $emailtemplateFactory,
        \Magento\Email\Model\BackendTemplate $emailbackendTemp
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_emailtemplateFactory = $emailtemplateFactory;
        $this->_emailbackendTemp = $emailbackendTemp;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::emailtemplate');
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_Helpdesk::emailtemplate')
            ->addBreadcrumb(__('EmailTemplate'), __('Events'))
            ->addBreadcrumb(__('Manage EmailTemplate'), __('Manage EmailTemplate'));
        return $resultPage;
    }

    /**
     * Edit Tickets
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $emailTempId = (int)$this->getRequest()->getParam('id');
        $emailtemplatemodel = $this->_emailbackendTemp;
        if ($emailTempId) {
            $emailtemplatemodel = $this->_emailbackendTemp->load($emailTempId);
            if (!$emailtemplatemodel->getId()) {
                $this->messageManager->addError(__('This Email Template no longer exists.'));
                $this->_redirect('helpdesk/*/');
                return;
            }
        }

        if (!$this->_coreRegistry->registry('email_template')) {
            $this->_coreRegistry->register('email_template', $emailtemplatemodel);
        }
        if (!$this->_coreRegistry->registry('current_email_template')) {
            $this->_coreRegistry->register('current_email_template', $emailtemplatemodel);
        }
        $resultPage = $this->_resultPageFactory->create();
        if (isset($emailTempId)) {
            $breadcrumb = __('Edit EmailTemplate');
            $resultPage->getConfig()->getTitle()->prepend(__('Edit EmailTemplate'));

        } else {
            $breadcrumb = __('New EmailTemplate');
            $resultPage->getConfig()->getTitle()->prepend(__('New EmailTemplate'));
        }
        return $resultPage;
    }
}
