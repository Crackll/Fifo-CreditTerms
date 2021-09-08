<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWalletSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWalletSystem\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Cart;

/**
 * Webkul MpWalletSystem Controller
 */
class Addwallettocart extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;
    
    /**
     * @var ProductFactory
     */
    protected $productFactory;
    
    /**
     * @var Magento\Checkout\Model\Cart
     */
    protected $cartModel;
    
    /**
     * @var \Webkul\MpWalletSystem\Helper\Data
     */
    protected $helper;
    
    /**
     * Initialize dependencies
     *
     * @param Context                                     $context
     * @param \Magento\Framework\Data\Form\FormKey        $formKey
     * @param PageFactory                                 $resultPageFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param ProductFactory                              $productFactory
     * @param cart                                        $cartModel
     * @param \Webkul\MpWalletSystem\Helper\Data          $helper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        PageFactory $resultPageFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ProductFactory $productFactory,
        cart $cartModel,
        \Webkul\MpWalletSystem\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->formKey = $formKey;
        $this->resultPageFactory = $resultPageFactory;
        $this->productFactory = $productFactory;
        $this->messageManager = $messageManager;
        $this->cartModel = $cartModel;
        $this->helper = $helper;
    }

    /**
     * Controller Execute function
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $wholedata = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        $price = $this->getRequest()->getParam('price');
        $baseCurrenyCode = $this->helper->getBaseCurrencyCode();
        $currencySymbol = $this->helper->getCurrencySymbol(
            $this->helper->getCurrentCurrencyCode()
        );
        $currentCurrenyCode = $this->helper->getCurrentCurrencyCode();
        $adminConfigPrice = $this->helper->getMinimumAmount();
        $finalminimumAmount = $this->helper->getwkconvertCurrency(
            $baseCurrenyCode,
            $currentCurrenyCode,
            $adminConfigPrice
        );
        if ($price < $finalminimumAmount) {
            $this->messageManager->addNotice(
                __(
                    'You can not add less than %1 amount to your wallet.',
                    $currencySymbol.$finalminimumAmount
                )
            );
            return $resultRedirect->setPath('mpwalletsystem/index/index');
        }
        if (array_key_exists('product', $wholedata)) {
            $params = [
                'form_key' => $this->formKey->getFormKey(),
                'product' =>$wholedata['product'],
                'qty'   =>1,
                'price' =>$wholedata['price']
            ];
            $resultRedirect->setPath('checkout/cart/add', $params);
        } else {
            $resultRedirect->setPath('mpwalletsystem/index/index');
        }
        return $resultRedirect;
    }
}
