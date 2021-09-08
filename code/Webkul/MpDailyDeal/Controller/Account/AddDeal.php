<?php
/**
 * Webkul MpDailyDeal addDeal controller.
 * @category  Webkul
 * @package   Webkul_MpDailyDeal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpDailyDeal\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class AddDeal extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * Marketplace Helper
     * @var \Webkul\Marketplace\Helper\Data
     */
    private $marketplaceHelper;
    
    /**
     * Helper
     *
     * @var \Webkul\MpDailyDeal\Helper\Data
     */
    private $helper;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var productloader
     */
    protected $_productloader;

    /**
     * @param Context $context
     * @param PageFactory $_resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $_resultPageFactory,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Webkul\MpDailyDeal\Helper\Data $helper,
        \Magento\CatalogInventory\Api\StockStateInterface $stockCollection,
        \Magento\Catalog\Model\ProductFactory $_productloader
    ) {
        $this->helper = $helper;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->_resultPageFactory = $_resultPageFactory;
        $this->_stockCollection  = $stockCollection;
        $this->_productloader = $_productloader;
        parent::__construct($context);
    }

    /**
     * Add Deal on product page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('id');
        $productName = $this->_productloader->create()->load($productId)
                                        ->getName();
        
        $isPartner = $this->marketplaceHelper->isSeller();
        if ($isPartner == 1 && $this->helper->isDealEnable()) {
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->_resultPageFactory->create();
            if ($this->marketplaceHelper->getIsSeparatePanel()) {
                $resultPage->addHandle('mpdailydeal_layout2_account_adddeal');
            }
            $resultPage->getConfig()->getTitle()->set(__('Add Deal On Product '). $productName);
            return $resultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
