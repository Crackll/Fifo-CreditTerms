<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\Ticketsmanagement\Type;

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
        \Webkul\Helpdesk\Model\TypeFactory $typeFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_typeFactory = $typeFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::type');
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
        $resultPage->setActiveMenu('Webkul_Helpdesk::type')
            ->addBreadcrumb(__('Type'), __('Type'))
            ->addBreadcrumb(__('Manage Type'), __('Manage Type'));
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
        $typeId = (int)$this->getRequest()->getParam('id');
        $typemodel = $this->_typeFactory->create();
        if ($typeId) {
            $typemodel->load($typeId);
            if (!$typemodel->getId()) {
                $this->messageManager->addError(__('This type no longer exists.'));
                $this->_redirect('helpdesk/*/');
                return;
            }
        }

        $this->_coreRegistry->register('helpdesk_ticket_type', $typemodel);

        if (isset($typeId)) {
            $breadcrumb = __('Edit Type');
        } else {
            $breadcrumb = __('New Type');
        }
        $this->_initAction()->addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->renderLayout();
    }
}
