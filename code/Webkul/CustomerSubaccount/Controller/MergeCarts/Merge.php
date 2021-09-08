<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomerSubaccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\CustomerSubaccount\Controller\MergeCarts;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Checkout\Model\Cart;

class Merge extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * Context
     *
     * @var \Magento\Framework\App\Action\Context
     */
    public $context;

    /**
     * Helper
     *
     * @var \Webkul\CustomerSubaccount\Helper\Data
     */
    public $helper;

    /**
     * Subaccount Cart Model
     *
     * @var \Webkul\CustomerSubaccount\Model\CartFactory
     */
    public $subaccCartFactory;

    /**
     * Quote Model
     *
     * @var \Magento\Quote\Model\QuoteFactory
     */
    public $quoteFactory;

    /**
     * Product Model
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $productFacrory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param \Magento\Catalog\Model\ProductFactory $productFacrory
     * @param Cart $cart
     */
    public function __construct(
        Context $context,
        \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        \Magento\Catalog\Model\ProductFactory $productFacrory,
        Cart $cart
    ) {
        $this->helper = $helper;
        $this->subaccCartFactory = $subaccCartFactory;
        $this->quoteFactory = $quoteFactory;
        $this->productFacrory = $productFacrory;
        $this->cart = $cart;
        parent::__construct($context);
    }
    
    public function execute()
    {
        if ($this->helper->isSubaccountUser()) {
            throw new NotFoundException(__('Action Not Allowed.'));
        }
        $data = $this->getRequest()->getParams();
        try {
            if (isset($data['id'])) {
                $this->subaccCartFactory->create()->load($data['id'], 'quote_id')->setStatus(1)->save();

                //add item to cart
                $quote = $this->quoteFactory->create()->load($data['id']);
                $items = $quote->getAllVisibleItems();
                foreach ($items as $item) {
                    $productId =$item->getProductId();
                    $_product = $this->productFacrory->create()->load($productId);
                    
                    $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                    $info = $options['info_buyRequest'];
                    $info['qty'] = $item->getQty();
                    $request = new \Magento\Framework\DataObject();
                    $request->setData($info);
                    $this->cart->addProduct($_product, $request);
                }
                $this->cart->save();
                $this->messageManager->addSuccess(__('Cart merged successfully.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $this->resultRedirectFactory->create()->setPath('wkcs/mergeCarts/index', ['cart'=>'update']);
    }
}
