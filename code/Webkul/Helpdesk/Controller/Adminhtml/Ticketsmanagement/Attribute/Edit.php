<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\Ticketsmanagement\Attribute;

use Magento\Backend\App\Action;

class Edit extends Action
{
    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Webkul\Helpdesk\Model\TicketsCustomAttributesFactory $ticketsCustomAttributesFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Webkul\Helpdesk\Model\TicketsCustomAttributesFactory $ticketsCustomAttributesFactory,
        \Magento\Eav\Model\Entity\AttributeFactory $eavEntityAttrFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_ticketsCustomAttributesFactory = $ticketsCustomAttributesFactory;
        $this->_eavEntityAttrFactory = $eavEntityAttrFactory;
        parent::__construct($context);
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
        $resultPage->setActiveMenu('Webkul_Helpdesk::customattribute')
            ->addBreadcrumb(__('Add Attribute'), __('Add Attribute'))
            ->addBreadcrumb(__('Manage Attribute'), __('Manage Attribute'));
        return $resultPage;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $attributeId = (int)$this->getRequest()->getParam('id');
        $attributemodel = $this->_eavEntityAttrFactory->create();
        if ($attributeId) {
            $customattrmodel = $this->_ticketsCustomAttributesFactory->create();
            $customattrmodel->load($attributeId);
            if (!$customattrmodel->getId()) {
                $this->messageManager->addError(__('This custom attribute no longer exists.'));
                $this->_redirect('helpdesk/*/');
                return;
            }
            $attributemodel->load($customattrmodel->getAttributeId());
            $attributemodel->setFieldDependency($customattrmodel->getFieldDependency());
            $attributemodel->setIsVisible($customattrmodel->getStatus());
            $attributemodel->setAttributeLabel($attributemodel->getFrontendLabel());
        }

        $this->_coreRegistry->register('helpdesk_ticket_attribute', $attributemodel);

        if (isset($attributeId)) {
            $breadcrumb = __('Edit Custom Attribute');
        } else {
            $breadcrumb = __('New Custom Attribute');
        }
        $this->_initAction()->addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->renderLayout();
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::customattribute');
    }
}
