<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Controller\Adminhtml\Unit;

use Magento\Framework\Locale\Resolver;

class Edit extends \Webkul\MpWholesale\Controller\Adminhtml\Unit
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Webkul\MpWholesale\Model\WholeSalerUnitFactory
     */
    private $wholeSalerUnitFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Webkul\MpWholesale\Model\WholeSalerUnitFactory $wholeSalerUnitFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\MpWholesale\Model\WholeSalerUnitFactory $wholeSalerUnitFactory
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->wholeSalerUnitFactory = $wholeSalerUnitFactory;
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
            ->addBreadcrumb(__('Wholesaler Unit'), __('Wholesaler Unit'));
        return $resultPage;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $wholeSalerUnitId=(int)$this->getRequest()->getParam('id');
        $wholeSalerUnitmodel=$this->wholeSalerUnitFactory->create();
        if ($wholeSalerUnitId) {
            $wholeSalerUnitmodel->load($wholeSalerUnitId);
            if (!$wholeSalerUnitmodel->getEntityId()) {
                $this->messageManager->addError(__('This unit no longer exists.'));
                $this->_redirect('mpwholesale/*/');
                return;
            }
        }
        $this->coreRegistry->register('wholesale_unitData', $wholeSalerUnitmodel);
        $resultPage = $this->_initAction();
        if ($wholeSalerUnitId) {
            $resultPage->addBreadcrumb(__('Edit Unit'), __('Edit Unit'));
            $resultPage->getConfig()->getTitle()->prepend(__('Update Unit'));
        } else {
            $resultPage->addBreadcrumb(__('Add Unit'), __('Add Unit'));
            $resultPage->getConfig()->getTitle()->prepend(__('Add Unit'));
        }
        return $resultPage;
    }
}
