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

use Webkul\WebApplicationFirewall\Integrations\AbuseIPDBInterface;
use Webkul\WebApplicationFirewall\Integrations\MailBoxLayerInterface;
use Webkul\WebApplicationFirewall\Api\Data\VulnerabilityInterface;

 /**
  * WAF Validator class
  */
class Validator extends AbstactValidator implements ValidatorInterface
{
    protected $logs = [];
    /**
     * @inheritDoc
     */
    public function setClientIps($ips)
    {
        $this->setData(self::CLIENT_IPS, $ips);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getClientIps()
    {
        return $this->getData(self::CLIENT_IPS);
    }

    /**
     * @inheritDoc
     */
    public function setRemoteIps($ips)
    {
        $this->setData(self::REMOTE_IPS, $ips);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRemoteIps()
    {
        return $this->getData(self::REMOTE_IPS);
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        $type = $this->getType();
        $isValid = false;
        if ($type == AbuseIPDBInterface::CODE) {
            $isValid = $this->abuseIPDBValidation();
        }
        if ($type == MailBoxLayerInterface::CODE) {
            if ($this->_checkValueAlreadyValidated($this->getEmail())) {
                $isValid = true;
            }
            if ($this->mailBoxLayer->validateEmail($this->getEmail())) {
                $this->_saveValidatedValue($this->getEmail());
                $isValid = true;
            }
        }
        return $isValid;
    }

    /**
     * Check Ip by AbuseIPDB
     *
     * @return VulnerabilityInterface
     */
    private function abuseIPDBValidation(): VulnerabilityInterface
    {
        $responses = [];
        $this->logs = ['message' => __('AbusIPDB processed')];
        $vulnerability = $this->vulnerabilityFactory->create();
        $checkIp = $this->integrationHelper
            ->getIntegrationData(
                'abuseipdb',
                'block_based_score'
            );
        if ($checkIp) {
            $requestIps = $this->getClientIps();
            $remoteIps = $this->getRemoteIps();

            if (!empty($remoteIps)) {
                $requestIps = $remoteIps;
            }

            foreach ($requestIps as $clientIp) {
                if ($this->_checkValueAlreadyValidated($clientIp)) {
                    continue;
                }
                $this->abuseIPDB->checkIp($clientIp);
                if (!$this->abuseIPDB->getResponse()) {
                    break;
                }
                $responses[] = $this->abuseIPDB->getResponse();
            }
            foreach ($responses as $response) {
                $isBlocked = $this->checkAbuseScore($response, $vulnerability);
                if ($isBlocked) {
                    break;
                } else {
                    $this->_saveValidatedValue($response['ipAddress']);
                }
            }
        }
        $vulnerability->setLogData($this->logs);

        return $vulnerability;
    }

    /**
     * checkAbuseScore function
     *
     * @param array $response
     * @return void
     */
    protected function checkAbuseScore(array $response, $vulnerability): bool
    {
        $abuseScore = $this->integrationHelper
            ->getIntegrationData(
                'abuseipdb',
                'abuse_score'
            );

        if (isset($response['abuseConfidenceScore']) &&
            $abuseScore &&
            $response['abuseConfidenceScore'] >= $abuseScore
        ) {
            $this->logs = ['message' => __('Blocked by AbusIPDB Score'), 'data' => $response];
            $vulnerability->setScore($response['abuseConfidenceScore'])
                ->setThreatType('AbusIPDB')
                ->setMessage(__('Blocked by AbusIPDB Score'));
            return true;
        }

        return false;
    }
}
