<?php
/**
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Controller\Account;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Webkul\MpWholesale\Helper\Data;

class Create extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Data
     */
    protected $wholesaleHelper;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     * @param Data        $wholesaleHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $wholesaleHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->wholesaleHelper = $wholesaleHelper;
        parent::__construct($context);
    }

    /**
     * Wholesaler register form page
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        if ($this->wholesaleHelper->getModuleStatus()) {
            $resultPage = $this->resultPageFactory->create();
            return $resultPage;
        } else {
            $this->messageManager->addError(__("Module is disabled by admin, Please contact to admin!"));
            return $this->resultRedirectFactory
                ->create()->setPath(
                    'customer/account',
                    ['_secure'=>$this->getRequest()->isSecure()]
                );
        }
    }
}
