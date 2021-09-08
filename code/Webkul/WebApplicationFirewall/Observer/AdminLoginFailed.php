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
namespace Webkul\WebApplicationFirewall\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Backend\Model\Session;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface;
use Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterfaceFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\Exception\LocalizedException;
use Webkul\WebApplicationFirewall\Integrations\AbuseIPDB;
use Magento\Framework\UrlInterface;

/**
 * WAF AdminLoginFailed class
 */
class AdminLoginFailed implements ObserverInterface
{
    const LOGIN_FAILED = 0;

    /**
     * @var \Webkul\WebApplicationFirewall\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\WebApplicationFirewall\Model\Notificator
     */
    protected $notificator;

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    protected $header;

    /**
     * @var SecurityLoginInterfaceFactory
     */
    protected $securityLoginFactory;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var Request
     */
    protected $httpRequest;

    /**
     * @var Iterator
     */
    protected $iterator;

    /**
     * @var array
     */
    protected $loginLogs = [];

    /**
     * @param \Webkul\WebApplicationFirewall\Helper\Data $helper
     * @param \Webkul\WebApplicationFirewall\Model\Notificator $notificator
     * @param Session $backendSession
     * @param SecurityLoginInterfaceFactory $securityLoginFactory
     * @param TimezoneInterface $timezone
     * @param Request $httpRequest
     * @param Iterator $iterator
     */
    public function __construct(
        \Webkul\WebApplicationFirewall\Helper\Data $helper,
        \Webkul\WebApplicationFirewall\Model\Notificator $notificator,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\HTTP\Header $header,
        SecurityLoginInterfaceFactory $securityLoginFactory,
        TimezoneInterface $timezone,
        UrlInterface $urlInterface,
        Request $httpRequest,
        Iterator $iterator,
        AbuseIPDB $abuseIpDb
    ) {
        $this->helper = $helper;
        $this->notificator = $notificator;
        $this->_redirect = $redirect;
        $this->header = $header;
        $this->securityLoginFactory = $securityLoginFactory;
        $this->timezone = $timezone;
        $this->urlInterface = $urlInterface;
        $this->httpRequest = $httpRequest;
        $this->iterator = $iterator;
        $this->abuseIpDb = $abuseIpDb;
    }

    /**
     * After login failed observer execute
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $isEnabled = $this->helper->getConfigData('brute_force', 'enable');
        if ($isEnabled) {
            $userName = $observer->getUserName();
            $clientIp = $this->httpRequest->getClientIp();
            $loginData = $this->httpRequest->getPost('login');

            $securityLogin = $this->securityLoginFactory->create();
            $securityLogin->setUsername($userName)
                ->setPassword($loginData['password'])
                ->setTime(time())
                ->setIp($clientIp)
                ->setBrowserAgent(
                    $this->helper->getBrowserData($this->header->getHttpUserAgent())
                )
                ->setUrl($this->urlInterface->getCurrentUrl())
                ->setRefererUrl($this->_redirect->getRefererUrl())
                ->setLoginStatus(self::LOGIN_FAILED)
                ->setIsSentMail(false);

            $alertOnEachFailed = $this->helper->getConfigData(
                'brute_force',
                'alert_each_login_failed'
            );

            if (!$alertOnEachFailed) {
                $this->_checkForBruteForce($securityLogin);
            }

            $securityLogin = $securityLogin->save();

            if (!$alertOnEachFailed) {
                $this->_sendBruteForceNotification($securityLogin);
            } else {
                $loginTime = $this->timezone->date(
                    $securityLogin->getTime()
                )->format('M d Y H:i:s');
                $securityLogin->setTime($loginTime);

                $this->_sendNotification([$securityLogin], (bool) $alertOnEachFailed);
            }
        }
    }

    /**
     * Check for the bruteforce
     *
     * @param SecurityLoginInterface $securityLogin
     * @return void
     */
    protected function _checkForBruteForce(SecurityLoginInterface $securityLogin)
    {
        $totalLoginFailed = $this->helper->getConfigData(
            'brute_force',
            'login_failed_count'
        );
        $loginFailedThreshold = $this->helper->getConfigData(
            'brute_force',
            'threshold'
        );

        $now = new \DateTime();
        $lockThreshInterval = new \DateInterval('PT' . $loginFailedThreshold .'M');
        $bruteForceTime = $now->sub($lockThreshInterval)->format('Y-m-d H:i:s');
        $collection = $this->securityLoginFactory->create()
            ->getCollection()
            ->addFieldToFilter('time', ['gteq' => $bruteForceTime])
            ->setOrder('entity_id', 'DESC');

        if ($collection->getSize() >= $totalLoginFailed) {
            $securityLogin->setIsBruteForce(1);
            $this->processIntegrationSecurity();
        }
    }

    /**
     * Send login failed email
     *
     * @param SecurityLoginInterface[] $securityLogin
     * @param bool $requireSend
     * @return void
     */
    protected function _sendNotification(
        array $securityLogin,
        $requireSend = false
    ) {
        if ($requireSend) {
            $sendToEmails = explode(',', $this->helper->getConfigData('brute_force', 'admin_emails'));
            $sendToEmails = array_map('trim', $sendToEmails);
            if (!empty(array_filter($sendToEmails))) {
                $this->notificator->sendLoginFailed(
                    $securityLogin,
                    $sendToEmails
                );
            }
        }
    }

    /**
     * Send notification based on login failed
     * within given time interval
     *
     * @param SecurityLoginInterface $securityLogin
     * @return void
     */
    protected function _sendBruteForceNotification(SecurityLoginInterface $securityLogin)
    {
        $securityLoginCollection = $this->securityLoginFactory->create()
            ->getCollection()
            ->addFieldToFilter('is_brute_force', 1);
        if ($securityLoginCollection->getSize()) {
            $totalLoginFailed = $this->helper->getConfigData(
                'brute_force',
                'login_failed_count'
            );
            $loginFailedThreshold = $this->helper->getConfigData(
                'brute_force',
                'threshold'
            );
            $loginLog = $securityLoginCollection->getFirstItem();
            $bruteForceDetectedTime = $loginLog->getTime();
            $bruteForceTime = $this->getBruteForceTime(
                $bruteForceDetectedTime,
                $loginFailedThreshold
            );
            $collection = $this->securityLoginFactory->create()
                ->getCollection()
                ->addFieldToFilter('time', ['gteq' => $bruteForceTime])
                ->setOrder('time', 'DESC');

            try {
                $this->iterator->walk(
                    $collection->getSelect(),
                    [[$this, 'getSecurityLoginModel']]
                );

                $this->_sendNotification(
                    $this->loginLogs,
                    true
                );

                $this->iterator->walk(
                    $collection->getSelect(),
                    [[$this, 'updateSecurityLoginModel']]
                );

            } catch (\Exception $e) {
                throw $e;
            }
        }
    }

    /**
     * Assign model objects to array
     *
     * @param array $args
     * @return void
     */
    public function getSecurityLoginModel($args)
    {
        $model = $this->securityLoginFactory->create();
        $time = $args['row']['time'];

        $args['row']['time'] = $this->timezone->date(
            $args['row']['time']
        )->format('M d Y H:i:s');

        $model->setData($args['row']);
        $this->loginLogs[] = $model;
    }

    /**
     * update SecurityLoginModel
     *
     * @param array $args
     * @return void
     */
    public function updateSecurityLoginModel($args)
    {
        $model = $this->securityLoginFactory->create();
        $model->setData($args['row']);
        $model->setIsSentMail(1);
        $model->setIsBruteForce(0);
        $model->getResource()->save($model);
    }

    /**
     * @param string $bruteForceDetectedTime
     * @param string $loginFailedThreshold
     * @return string
     */
    private function getBruteForceTime($bruteForceDetectedTime, $loginFailedThreshold) : string
    {
        $detectedTime = new \DateTime($bruteForceDetectedTime);
        $lockThreshInterval = new \DateInterval('PT' . $loginFailedThreshold .'M');
        $bruteForceTime = $detectedTime->sub($lockThreshInterval)->format('Y-m-d H:i:s');
        return $bruteForceTime;
    }

    /**
     * Report and validate ips
     * 18 and 4 are report categories
     * 18 for Brute Force
     * 4 is DDoS Attack
     *
     * @return void
     */
    private function processIntegrationSecurity()
    {
        $clientIp = $this->httpRequest->getClientIp();
        $isReportIp = $this->helper->getConfigData(
            'abuseipdb',
            'report_ip'
        );
        if ($clientIp && $isReportIp) {
            $this->abuseIpDb->reportIp($clientIp, ['18', '4']);
        }
    }
}
