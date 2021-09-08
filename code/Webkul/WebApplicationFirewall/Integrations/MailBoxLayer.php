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

namespace Webkul\WebApplicationFirewall\Integrations;

use Webkul\WebApplicationFirewall\Helper\Integration;
use Magento\Framework\HTTP\Client\Curl;

/**
 * @method mixed getResponse()
 */
class MailBoxLayer extends \Magento\Framework\DataObject implements MailBoxLayerInterface
{

    /**
     * @var \Magento\Framework\HTTP\Adapter\CurlFactory
     *
     */
    protected $curlFactory;

    /** @var string */
    private $apiKey;

    /**
     * @var Integration
     */
    protected $helper;

    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    public function __construct(
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Psr\Log\LoggerInterface $logger,
        Integration $helper
    ) {
        $this->curlFactory  = $curlFactory;
        $this->helper       = $helper;
        $this->_logger      = $logger;
        $this->initialize();
    }

    /**
     * Initialize
     *
     * @return void
     */
    private function initialize()
    {
        $this->apiKey = $this->helper->getIntegrationData(
            self::CODE,
            'api_key'
        );
    }
    
    /**
     * Get Api key
     *
     * @return string
     */
    protected function _getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @inheritDoc
     */
    public function validateEmail($emailAddress)
    {
        $curl = $this->curlFactory->create();
        $url = self::SERVER_URL.'?access_key='.$this->_getApiKey().'&email='.$emailAddress;
        $curl->write(\Zend_Http_Client::GET, $url, '1.0');
        $data = $curl->read();
        $data = preg_split('/^\r?$/m', $data, 2);
        $data = trim($data[1]);
        $curl->close();
        try {
            $isValid = false;
            $json = $this->helper->jsonDecode($data);
            
            if ($json['smtp_check'] && $json['score'] > 0.5) {
                $isValid = true;
            }
        } catch (\Exception $e) {
            return false;
        }
        return $isValid;
    }

    /**
     * Set api response
     *
     * @param array $response
     * @return $this
     */
    protected function _setResponse($response = [])
    {
        $this->setData('response', $response);
        return $this;
    }
}
