<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 *
 */
namespace Webkul\WebApplicationFirewall\App\Action\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Backend\App\AbstractAction;
use Magento\Framework\UrlInterface;
use Webkul\WebApplicationFirewall\Model\Validators\IpValidatorInterface;

/**
 * Authentication Plugin class
 */
class Authentication
{
    /**
     * @var \Webkul\WebApplicationFirewall\Model\AdminLoginFactory
     */
    protected $_loginUserFactory;

    /**
     * @var \Webkul\WebApplicationFirewall\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\WebApplicationFirewall\Helper\ErrorProcessor
     */
    protected $errorProcessor;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    protected $header;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var UrlInterface
     */
    protected $authStorage;

    /**
     * @var \Magento\Backend\Model\Auth\StorageInterface
     */
    protected $urlInterface;

    /**
     * @var IpValidatorInterface
     */
    protected $ipValidator;

    /**
     * @param \Webkul\WebApplicationFirewall\Model\AdminLoginFactory $loginUserFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Webkul\WebApplicationFirewall\Helper\Data $helper
     * @param \Webkul\WebApplicationFirewall\Helper\ErrorProcessor $errorProcessor
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Framework\HTTP\Header $header
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Backend\Model\Auth\StorageInterface $authStorage
     * @param UrlInterface $urlInterface
     * @param IpValidatorInterface $ipValidator
     */
    public function __construct(
        \Webkul\WebApplicationFirewall\Model\AdminLoginFactory $loginUserFactory,
        \Webkul\WebApplicationFirewall\Helper\Data $helper,
        \Webkul\WebApplicationFirewall\Helper\ErrorProcessor $errorProcessor,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\HTTP\Header $header,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Backend\Model\Auth\StorageInterface $authStorage,
        UrlInterface $urlInterface,
        IpValidatorInterface $ipValidator
    ) {
        $this->_loginUserFactory = $loginUserFactory;
        $this->helper = $helper;
        $this->errorProcessor = $errorProcessor;
        $this->backendSession = $backendSession;
        $this->header = $header;
        $this->_urlInterface = $urlInterface;
        $this->_redirect = $redirect;
        $this->ipValidator = $ipValidator;
        $this->authStorage = $authStorage;
    }

    /**
     * Process login action validate
     *
     * @param AbstractAction $subject
     * @param RequestInterface $request
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(AbstractAction $subject, RequestInterface $request)
    {
        $key = $request->getPost('login');
        if ($key) {
            $isEnabled = $this->helper->getConfigData('brute_force', 'enable');
            $this->backendSession->setUrl($this->_urlInterface->getCurrentUrl());
            $this->backendSession->setRefererUrl($this->_redirect->getRefererUrl());
            $this->backendSession->setBrowser(
                $this->helper->getBrowserData($this->header->getHttpUserAgent())
            );
            $this->backendSession->setClientIp($request->getClientIp());
            $this->backendSession->setLoginData($request->getPostValue('login'));
            $this->validateIp($request);
        }
    }

    /**
     * Validate client ip
     *
     * @param RequestInterface $request
     * @return void
     */
    private function validateIp(RequestInterface $request)
    {
        $requestIps = array_filter(array_map('trim', explode(',', $request->getClientIp())));
        $this->ipValidator->setClientIps($requestIps);
        if (!$this->ipValidator->validate()) {
            $request->setPostValue('login', null);
            if ($this->authStorage->isLoggedIn()) {
                $this->authStorage->processLogout();
            }
            $this->errorProcessor->processIpBlockError('WAF_IP_BLOCKED', __('Your ip is blocked by admin.'));
        }
    }

    /**
     * check the device is blocked for login
     *
     * @param RequestInterface $request
     * @return bool
     */
    private function isValidDevice(RequestInterface $request)
    {
        $deviceId = $request->getPost('device_uuid');
        if ($deviceId) {
            $deviceData = $this->_loginUserFactory->create()->loadByDeviceId($deviceId);
            if ($deviceData->getId() && $deviceData->getReportedUnknown()) {
                return false;
            }
        }

        return true;
    }
}
