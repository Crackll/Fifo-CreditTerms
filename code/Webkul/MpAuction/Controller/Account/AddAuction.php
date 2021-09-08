<?php
/**
 * Webkul MpAuction addAuction controller.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\MpAuction\Model\ProductFactory as AuctionProductFactory;

class AddAuction extends \Magento\Customer\Controller\AbstractAccount
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
        \Webkul\Marketplace\Helper\Data $marketPlaceHelper,
        \Magento\Catalog\Model\Product $product,
        AuctionProductFactory $auctionProduct,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->marketPlaceHelper=$marketPlaceHelper;
        $this->resultPageFactory = $_resultPageFactory;
        $this->_datahelper = $dataHelper;
        $this->product = $product;
        $this->auctionProduct = $auctionProduct;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * Add Auction on product page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $isPartner = $this->marketPlaceHelper->isSeller();
        if ($isPartner == 1 && $this->_datahelper->isAuctionEnable()) {
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $auctionId = (int) $this->getRequest()->getParam('aid');
            $productId = (int) $this->getRequest()->getParam('pid');
            $auctionProduct = $this->auctionProduct->create();
            $productName = "";
            if ($auctionId) {
                $auctionProduct = $auctionProduct->load($auctionId);
                $product = $this->product->load($auctionProduct->getProductId());
                $productName = $product->getName();
                if (!$auctionProduct->getEntityId()) {
                    $this->messageManager->addErrorMessage(__('Auction no longer exist.'));
                    return $this->resultRedirectFactory->create()->setPath(
                        'mpauction/account/index',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } elseif ($productId) {
                $product = $this->product->load($productId);
                $productName =  $product->getName();
            }
            $resultPage = $this->resultPageFactory->create();
            if ($this->marketPlaceHelper->getIsSeparatePanel()) {
                $resultPage->addHandle('mpauction_layout2_account_addauction');
            }
            $resultPage->getConfig()->getTitle()->set(__('Add Auction On ').$productName);
            return $resultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
