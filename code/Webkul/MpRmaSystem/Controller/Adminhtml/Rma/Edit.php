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
namespace Webkul\MpRmaSystem\Controller\Adminhtml\Rma;

use Magento\Framework\Controller\ResultFactory;

class Edit extends \Webkul\MpRmaSystem\Controller\Adminhtml\Rma
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Webkul\MpRmaSystem\Model\DetailsFactory
     */
    protected $details;

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
        \Webkul\MpRmaSystem\Model\DetailsFactory $details,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->backendSession = $context->getSession();
        $this->details = $details;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $detailsModel = $this->details->create();
        if ($this->getRequest()->getParam('id')) {
            $detailsModel->load($this->getRequest()->getParam('id'));
        }

        $data = $this->backendSession->getFormData(true);
        if (!empty($data)) {
            $detailsModel->setData($data);
        }

        $this->coreRegistry->register('mprmasystem_rma', $detailsModel);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Details'));
        return $resultPage;
    }

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpRmaSystem::rma');
    }
}
