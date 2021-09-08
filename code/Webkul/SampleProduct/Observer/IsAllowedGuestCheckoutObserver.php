<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SampleProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SampleProduct\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Checks if guest checkout is allowed then quote contains sample products.
 */
class IsAllowedGuestCheckoutObserver implements ObserverInterface
{

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Webkul\SampleProduct\Helper\Data $helper
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Framework\App\Response\Http $response,
        \Webkul\SampleProduct\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Psr\Log\LoggerInterface $logger = null
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->_urlFactory = $urlFactory;
        $this->response = $response;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
        $this->logger = $logger ?: ObjectManager::getInstance()
            ->get(\Psr\Log\LoggerInterface::class);
    }

    /**
     * Check is allowed guest checkout if quote contain sample product(s)
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        try {
            if (!$this->helper->isSampleProductEnable()) {
                return $this;
            }
            if (!$this->helper->allowToLoginCustomer()) {
                return $this;
            }
            $result = $observer->getEvent()->getResult();

            /* @var $quote Quote */
            $quote = $observer->getEvent()->getQuote();

            $isSampleProductInCart = 0;

            foreach ($quote->getAllItems() as $item) {
                $product = $item->getProduct();

                if ((string)$product->getTypeId() === 'sample') {
                    $isSampleProductInCart = 1;
                    $result->setIsAllowed(false);
                    break;
                }
            }

            if ($isSampleProductInCart && $this->customerSession->isLoggedIn()) {
                $customerData = $this->customerSession->getData();
                $customerGroupId = $customerData['customer_group_id'];

                $allowCustomerGroups = explode(',', $this->helper->allowCustomerGroups());

                if (!in_array($customerGroupId, $allowCustomerGroups)) {
                    $this->messageManager->addErrorMessage(
                        __('This Customer group is not allowed to buy sample products')
                    );
                    $this->customerSession->logout();
                    $url = $this->_createUrl()->getUrl('customer/account/login');
                    $this->response->setRedirect($url);

                    return false;
                }
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e);
            $this->checkoutSession->setQuoteId(null);
        }

        return $this;
    }

    /**
     * Creates URL object
     *
     * @return \Magento\Framework\UrlInterface
     */
    protected function _createUrl()
    {
        return $this->_urlFactory->create();
    }
}
