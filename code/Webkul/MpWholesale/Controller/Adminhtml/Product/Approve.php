<?php
/**
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Webkul\MpWholesale\Model\ProductFactory;
use Webkul\MpWholesale\Helper\Email;

/**
 * Class WholeSale Approve
 */
class Approve extends \Magento\Backend\App\Action
{
    const STATUS_ENABLE   = 1;
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Email
     */
    protected $emailHelper;

    /**
     * @param Context         $context
     * @param ProductFactory  $productFactory
     * @param Email           $emailHelper
     */
    public function __construct(
        Context $context,
        Email $emailHelper,
        ProductFactory $productFactory
    ) {
        $this->emailHelper = $emailHelper;
        $this->productFactory = $productFactory;
        parent::__construct($context);
    }
    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $productModel = $this->productFactory->create()->load($id);
        try {
            if ($productModel->getEntityId()) {
                $productModel->setApproveStatus(self::STATUS_ENABLE)->save();
                $this->emailHelper->sendProductApprovalMail($id);
                $this->messageManager->addSuccess(__('Product has been Approved.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        // /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
    }
    
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpWholesale::productlist');
    }
}
