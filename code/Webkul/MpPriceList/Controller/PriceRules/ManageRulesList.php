<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpPriceList
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPriceList\Controller\PriceRules;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Webkul\MpPriceList\Helper\Data;
use Magento\Framework\Controller\ResultFactory;
use Webkul\Marketplace\Helper\Data as MpHelper;

class ManageRulesList extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var Webkul\MpPriceList\Helper\Data
     */
    protected $_priceListHelper;

    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    private $mpHelper;

   /**
    * @param Context $context
    * @param PageFactory $resultPageFactory
    * @param Data $priceListHelper
    * @param MpHelper $mpHelper
    */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $priceListHelper,
        MpHelper $mpHelper
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_priceListHelper = $priceListHelper;
        $this->mpHelper = $mpHelper;
    }

    /**
     * Seller Manage Rules List page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->mpHelper;
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            if ($this->_priceListHelper->isModuleEnabled()) {
                /** @var \Magento\Framework\View\Result\Page $resultPage */
                $resultPage = $this->_resultPageFactory->create();
                if ($this->mpHelper->getIsSeparatePanel()) {
                    $resultPage->addHandle('mppricelist_pricerules_manageruleslist_layout2');
                }
                $resultPage->getConfig()->getTitle()->set(__('Manage Price Rules'));
                return $resultPage;
            } else {
                $this->messageManager->addError(__("Pricelist is disabled by admin, Please contact to admin!"));
                return $this->resultRedirectFactory
                    ->create()->setPath(
                        'customer/account/',
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
