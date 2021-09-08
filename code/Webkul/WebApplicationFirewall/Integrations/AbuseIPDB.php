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
use GuzzleHttp\Client;

/**
 * @method mixed getResponse()
 */
class AbuseIPDB extends \Magento\Framework\DataObject implements AbuseIPDBInterface
{

    /** @var Client */
    private $client;

    /** @var string */
    private $apiKey;

    /**
     * @var Integration
     */
    protected $helper;

    public function __construct(
        Integration $helper
    ) {
        $this->helper = $helper;
        $this->createClient();
    }

    /**
     * Create connection
     *
     * @return void
     */
    private function createClient()
    {
        if (!$this->client) {
            $this->client = new Client([
                'base_uri' => self::SERVER_URL
            ]);
            $this->apiKey = $this->helper->getIntegrationData(
                'abuseipdb',
                'api_key'
            );
            $this->_logger = $this->helper->getAbuseIPDBLogger();
        }
    }
    
    /**
     * Get client object
     *
     * @return Client
     */
    protected function _getClient()
    {
        return $this->client;
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
    public function checkIp($ip)
    {
        try {
            $maxDays = $this->helper->getIntegrationData(self::CODE, 'max_days');
            $response = $this->_getClient()->request('GET', 'check', [
                'query' => [
                    'ipAddress' => $ip,
                    'maxAgeInDays' => $maxDays,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Key' => $this->_getApiKey()
                ],
            ]);
            $output = $this->helper->jsonDecode($response->getBody());
            
            if (isset($output['data'])) {
                $this->_setResponse($output['data']);
            } else {
                $this->_logger->info($response->getBody());
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getBlackListedIps()
    {
        $response = $this->_getClient()->request('GET', 'blacklist', [
            'query' => [
                'confidenceMinimum' => '90'
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Key' => $this->_getApiKey()
            ],
        ]);
        
        $output = $this->helper->jsonDecode($response->getBody());
        
        if (isset($output['data'])) {
            $this->_setResponse($output['data']);
        }
        
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function reportIp($ip, $categories = [])
    {
        $response = $this->_getClient()->request('POST', 'report', [
            'query' => [
                'ip' => $ip,
                'categories' => implode(',', $categories),
                'comment' => 'Credential brute-force attacks on webpage logins.'
            ],
            'headers' => [
                    'Accept' => 'application/json',
                    'Key' => $this->_getApiKey()
            ],
        ]);
        
        $output = $this->helper->jsonDecode($response->getBody());
        if (isset($output['data'])) {
            $this->_setResponse($output['data']);
        }
    }

    /**
     * @inheritDoc
     */
    public function bulkReport($ip)
    {
        return $this;
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
