<?php
/**
 * Webkul MpAuction detail controller.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class AuctionBidDetail extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Webkul\MpAuction\Helper\Data
     */
    protected $_datahelper;

    /**
     * @param Context $context
     * @param PageFactory $_resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $_resultPageFactory,
        \Webkul\MpAuction\Helper\Data $dataHelper,
        \Webkul\Marketplace\Helper\Data $marketPlaceHelper
    ) {
        $this->marketPlaceHelper=$marketPlaceHelper;
        $this->resultPageFactory = $_resultPageFactory;
        $this->_datahelper = $dataHelper;
        parent::__construct($context);
    }

    /**
     * Auction Bid Detail page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $aucId = $this->getRequest()->getParam('id');
        $checkAuctionAvail = $this->_datahelper->checkAuctionAvail($aucId);
        $isPartner = $this->marketPlaceHelper->isSeller();
        if ($isPartner == 1 && $this->_datahelper->isAuctionEnable()) {
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            if (!empty($checkAuctionAvail)) {
                $resultPage = $this->resultPageFactory->create();
                if ($this->marketPlaceHelper->getIsSeparatePanel()) {
                    $resultPage->addHandle('mpauction_layout2_account_auctionbiddetail');
                }
                $resultPage->getConfig()->getTitle()->set(__('Auction Bid Detail'));
                return $resultPage;

            } else {
                $this->messageManager->addErrorMessage(__('Auction no longer exist.'));
                return $this->resultRedirectFactory->create()->setPath(
                    'mpauction/account/auctionlist',
                    ['_secure' => $this->getRequest()->isSecure()]
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
