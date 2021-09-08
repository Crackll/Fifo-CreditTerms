<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Controller\Quotation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Webkul\MpWholesale\Model\QuotesFactory;
use Webkul\MpWholesale\Helper\Data;
use Magento\Framework\Controller\ResultFactory;

class Edit extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var QuotesFactory
     */
    protected $quotesFactory;
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;
    /**
     * @var Data
     */
    protected $wholesaleHelper;

    /**
     * @param Context                         $context
     * @param PageFactory                     $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param QuotesFactory                   $quotesFactory
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param Data                            $wholesaleHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        QuotesFactory $quotesFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        Data $wholesaleHelper
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->quotesFactory = $quotesFactory;
        $this->mpHelper = $mpHelper;
        $this->wholesaleHelper = $wholesaleHelper;
    }
    
    /**
     * Default Quote edit page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $isPartner = $this->mpHelper->isSeller();
        if ($isPartner == 1) {
            if ($this->wholesaleHelper->getModuleStatus()) {
                $wholedata = $this->getRequest()->getParams();
                $entityId = 0;
                if (array_key_exists('id', $wholedata)) {
                    $entityId = $wholedata['id'];
                }
                if ($entityId) {
                    $quoteModel = $this->quotesFactory->create()->load($entityId);
                    if ($quoteModel) {
                        $quoteSellerId = $quoteModel->getSellerId();
                        $sellerId = $this->mpHelper->getCustomerId();
                        if ($quoteSellerId!=$sellerId) {
                            $this->messageManager->addError(__('You are not authorized to access this quote'));
                            return $this->resultRedirectFactory
                                ->create()->setPath(
                                    '*/*/view',
                                    ['_secure'=>$this->getRequest()->isSecure()]
                                );
                        }
                        /** @var \Magento\Framework\View\Result\Page $resultPage */
                        $resultPage = $this->resultPageFactory->create();
                        if ($this->mpHelper->getIsSeparatePanel()) {
                            $resultPage->addHandle('mpwholesale_layout2_quotation_edit');
                        }
                        $resultPage->getConfig()->getTitle()->set(__('Edit Quote'));
                        return $resultPage;
                    } else {
                        $this->messageManager->addError(__('Quote Does Not exists.'));
                    }
                } else {
                    $this->messageManager->addError(__('Quote Does Not exists.'));
                }
                return $this->resultRedirectFactory
                    ->create()->setPath(
                        '*/*/view',
                        ['_secure'=>$this->getRequest()->isSecure()]
                    );
            } else {
                $this->messageManager->addError(__("Module is disabled by admin, Please contact to admin!"));
                return $this->resultRedirectFactory
                    ->create()->setPath(
                        'customer/account',
                        ['_secure'=>$this->getRequest()->isSecure()]
                    );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
