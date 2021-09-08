<?php
/**
 * Webkul MpAuction addDeal controller.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class AuctionList extends \Magento\Customer\Controller\AbstractAccount
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
        PageFactory $resultPageFactory,
        \Webkul\MpAuction\Helper\Data $dataHelper,
        \Webkul\Marketplace\Helper\Data $marketPlaceHelper
    ) {
        $this->marketPlaceHelper=$marketPlaceHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->_datahelper = $dataHelper;
        parent::__construct($context);
    }

    /**
     * Deal List page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $isPartner = $this->marketPlaceHelper->isSeller();
        if ($isPartner == 1 && $this->_datahelper->isAuctionEnable()) {
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            if ($this->marketPlaceHelper->getIsSeparatePanel()) {
                $resultPage->addHandle('mpauction_layout2_account_auctionlist');
            }
            $resultPage->getConfig()->getTitle()->set(__('Auction List'));
            return $resultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
