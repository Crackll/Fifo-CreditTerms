<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\ConnectEmail;

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
        \Webkul\Helpdesk\Model\ConnectEmailFactory $connectEmailFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_connectEmailFactory = $connectEmailFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::connectemail');
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
        $resultPage->setActiveMenu('Webkul_Helpdesk::connectemail')
            ->addBreadcrumb(__('Connect Email'), __('Connect Email'))
            ->addBreadcrumb(__('Manage Connect Email'), __('Manage Connect Email'));
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
        $cemailId = (int)$this->getRequest()->getParam('id');
        $cemailIdmodel = $this->_connectEmailFactory->create();
        if ($cemailId) {
            $cemailIdmodel->load($cemailId);
            if (!$cemailIdmodel->getId()) {
                $this->messageManager->addError(__('This Event no longer exists.'));
                $this->_redirect('helpdesk/*/');
                return;
            }
        }

        $this->_coreRegistry->register('helpdesk_connectemail', $cemailIdmodel);
        $resultPage = $this->_resultPageFactory->create();
        if ($cemailIdmodel->getId()) {
            $breadcrumb = __('Edit Connect Email');
            $resultPage->getConfig()->getTitle()->prepend(__('Edit Connect Email'));

        } else {
            $breadcrumb = __('New Connect Email');
            $resultPage->getConfig()->getTitle()->prepend(__('New Connect Email'));
        }
        return $resultPage;
    }
}
