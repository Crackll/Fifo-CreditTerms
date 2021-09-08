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

namespace Webkul\WebApplicationFirewall\Helper;

use Magento\Customer\Model\SessionFactory;
use OTPHP\TOTP;
use Base32\Base32;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;
use Magento\Framework\HTTP\PhpEnvironment\Request;

/**
 * WAF OTP Helper
 */
class OTPHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Session
     */
    private $_customerSession;

    /**
     * @var Request
     */
    protected $_request;

    /**
     * Constructor
     * @param Magento\Framework\App\Helper\Context      $context
     * @param Request                                   $request
     * @param SessionFactory                            $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Request $request,
        SessionFactory $customerSession
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_request = $request;
    }

    /**
     * create TOTP Object
     * @param string $customerEmail
     * @return object
     */
    public function getTOTP($customerEmail)
    {
        $session = $this->_customerSession->create();
        $secretKey = $session->getTOTPSecretKey();
        if (!$secretKey) {
            $secretKey = $this->getSecretKey();
            $session->setTOTPSecretKey($secretKey);
        }

        $totp = TOTP::create($secretKey);
        $totp->setLabel($customerEmail);
        $ip = $this->_request->getClientIp();
        $httpHost = $this->_request->getHttpHost();
        if ($ip == $httpHost || !$httpHost) {
            $totp->setIssuer("Localhost");
        } else {
            $totp->setIssuer($httpHost);
        }

        return $totp;
    }

    /**
     * create TOTP Object by secret key
     * @param string $secretKey
     * @param string $customerEmail
     * @return object
     */
    public function getTOTPBySecretKey($secretKey, $customerEmail)
    {
        $totp = TOTP::create($secretKey);
        $totp->setLabel($customerEmail);
        $ip = $this->_request->getClientIp();
        $httpHost = $this->_request->getHttpHost();
        if ($ip == $httpHost || !$httpHost) {
            $totp->setIssuer("Localhost");
        } else {
            $totp->setIssuer($httpHost);
        }

        return $totp;
    }

    /**
     * get Qrcode
     * @param string $customerEmail
     * @return string
     */
    public function getQrcode($customerEmail)
    {
        $totp = $this->getTOTP($customerEmail);
        /* @codingStandardsIgnoreStart */
        $qrCode = new QrCode($totp->getProvisioningUri());
        /* @codingStandardsIgnoreEnd */
        $qrCode->setSize(200);
        return $qrCode->writeDataUri();
    }

    /**
     * Generate secret key
     * @param void
     * @return string
     */
    private function getSecretKey()
    {
        $secret = random_bytes(128);
        return preg_replace('/[^A-Za-z0-9]/', '', \Base32\Base32::encode($secret));
    }
}
