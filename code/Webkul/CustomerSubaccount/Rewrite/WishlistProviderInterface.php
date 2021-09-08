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
namespace Webkul\CustomerSubaccount\Rewrite;

use Magento\Framework\App\RequestInterface;

class WishlistProviderInterface extends \Magento\Wishlist\Controller\WishlistProvider
{
    /**
     * @var \Magento\Wishlist\Model\Wishlist
     */
    protected $wishlist;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Helper
     *
     * @var \Webkul\CustomerSubaccount\Helper\Data
     */
    public $helper;

    /**
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlistFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param RequestInterface $request
     */
    public function __construct(
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        RequestInterface $request
    ) {
        $this->request = $request;
        $this->wishlistFactory = $wishlistFactory;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
        $this->helper = $helper;
        parent::__construct($wishlistFactory, $customerSession, $messageManager, $request);
    }

    /**
     * @inheritdoc
     */
    public function getWishlist($wishlistId = null)
    {
        if ($this->wishlist) {
            return $this->wishlist;
        }
        if ($this->request->getRouteName()=='wkcs') {
            $wishlist = $this->wishlistFactory->create();
            $customerId = $this->customerSession->getCustomerId();
            $mainAccId = $this->helper->getMainAccountId($customerId);
            $wishlist->loadByCustomerId($mainAccId, true);
        } else {
            $wishlist = parent::getWishlist($wishlistId);
        }
        $this->wishlist = $wishlist;
        return $wishlist;
    }
}
