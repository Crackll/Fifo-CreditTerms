<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\WebApplicationFirewall\Controller\Account;

use Magento\Framework\Url\DecoderInterface;

class Login extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var $_urlDecoder
     */
    protected $_urlDecoder;

    /**
     * Construct
     * @param \Magento\Framework\App\Action\Context             $context
     * @param \Magento\Framework\View\Result\PageFactory        $resultPageFactory
     * @param \Magento\Framework\Encryption\EncryptorInterface  $encryptor
     * @param \Magento\Customer\Model\SessionFactory            $customerSession
     * @param \Psr\Log\LoggerInterface                          $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Psr\Log\LoggerInterface $logger,
        DecoderInterface $urlDecoder
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_encryptor = $encryptor;
        $this->_customerSession = $customerSession;
        $this->_logger = $logger;
        $this->_urlDecoder = $urlDecoder;
        parent::__construct($context);
    }

    /**
     * Execute view action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->_customerSession->create()->isLoggedIn()) {
            $this->_redirect('customer/account/');
        }

        $param = $this->getRequest()->getParam('param');
        $customerId = 0;
        if ($param) {
            try {
                $customerId = (int)$this->_encryptor->decrypt($this->_urlDecoder->decode($param));
            } catch (\Exception $ex) {
                $this->_logger->info($ex->getMessage());
            }
            if ($customerId) {
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->set(__('Two Step Authentication'));
                return $resultPage;
            }
        }

        $this->_redirect('customer/account/');
    }
}
