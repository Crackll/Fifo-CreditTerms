<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRmaSystem\Controller\Adminhtml\Reasons;

use Webkul\MpRmaSystem\Controller\Adminhtml\Reasons as ReasonsController;
use Magento\Framework\Controller\ResultFactory;

class Edit extends ReasonsController
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Webkul\MpRmaSystem\Model\ReasonsFactory
     */
    protected $reasons;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\MpRmaSystem\Model\ReasonsFactory $reasons
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\MpRmaSystem\Model\ReasonsFactory $reasons,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->backendSession = $context->getSession();
        $this->reasons = $reasons;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $reasonModel = $this->reasons->create();
        if ($this->getRequest()->getParam('id')) {
            $reasonModel->load($this->getRequest()->getParam('id'));
        }

        $data = $this->backendSession->getFormData(true);
        if (!empty($data)) {
            $reasonModel->setData($data);
        }

        $this->coreRegistry->register('mprmasystem_reasons', $reasonModel);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Reasons'));
        $resultPage->getConfig()->getTitle()->prepend(
            $reasonModel->getId() ? $reasonModel->getTitle() : __('New Reason')
        );
        $resultPage->addBreadcrumb(__('Manage Reasons'), __('Manage Reasons'));
        $block = \Webkul\MpRmaSystem\Block\Adminhtml\Reasons\Edit::class;
        $content = $resultPage->getLayout()->createBlock($block);
        $resultPage->addContent($content);
        return $resultPage;
    }

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpRmaSystem::reasons');
    }
}
