<?php
/**
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Controller\Adminhtml\Product;

use Magento\Framework\Locale\Resolver;

class Edit extends \Webkul\MpWholesale\Controller\Adminhtml\Product
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
     * @var \Webkul\MpWholesale\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Webkul\MpWholesale\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\MpWholesale\Model\ProductFactory $productFactory
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->productFactory = $productFactory;
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
            ->addBreadcrumb(__('Products'), __('Products'));
        return $resultPage;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $flag = 0;
        $entityId=(int)$this->getRequest()->getParam('id');
        $productmodel=$this->productFactory->create();
        if ($entityId) {
            $productmodel->load($entityId);
            if (!$productmodel->getEntityId()) {
                $this->messageManager->addError(__('This Rule for this product is no longer exists.'));
                $this->_redirect('mpwholesale/*/');
                return;
            }
        }
        $this->coreRegistry->register('wholesale_productData', $productmodel);
        $resultPage = $this->_initAction();
        if ($entityId) {
            $resultPage->addBreadcrumb(__('Edit Product Rule'), __('Edit Product Rule'));
            $resultPage->getConfig()->getTitle()->prepend(__('Update Product Rule'));
        } else {
            $resultPage->addBreadcrumb(__('Add Product Rule'), __('Add Product Rule'));
            $resultPage->getConfig()->getTitle()->prepend(__('Add Product Rule'));
        }
        return $resultPage;
    }
}
