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

namespace Webkul\CustomerSubaccount\Controller\WishList;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Exception\NotFoundException;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class AddToMain extends \Magento\Customer\Controller\AbstractAccount
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
     * Item Model
     *
     * @var \Magento\Wishlist\Model\ItemFactory
     */
    public $itemFactory;

    /**
     * Wishlist Provider
     *
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    public $wishlistProvider;

    /**
     * Product Repository
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    public $productRepository;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param \Magento\Wishlist\Model\ItemFactory $itemFactory
     * @param WishlistProviderInterface $wishlistProvider
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        \Magento\Wishlist\Model\ItemFactory $itemFactory,
        WishlistProviderInterface $wishlistProvider,
        ProductRepositoryInterface $productRepository
    ) {
        $this->helper = $helper;
        $this->itemFactory = $itemFactory;
        $this->wishlistProvider = $wishlistProvider;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $data =  $this->getRequest()->getParams();
        if (!$this->helper->canAddToMainWishlist() || !isset($data['id'])) {
            throw new NotFoundException(__('Action Not Allowed.'));
        }
        $item = $this->itemFactory->create()->load($data['id']);
        $wishlist = $this->wishlistProvider->getWishlist();
        $item->setWishlistId($wishlist->getId())->save();
        $wishlist->save();
        $this->messageManager->addSuccess(__('Item added to Main Wish List.'));
        return $this->resultRedirectFactory->create()->setPath('wishlist');
    }
}
