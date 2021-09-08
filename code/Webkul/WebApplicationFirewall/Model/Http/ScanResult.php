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
namespace Webkul\WebApplicationFirewall\Model\Http;

use Webkul\WebApplicationFirewall\Api\ScanHttpResultInterface;

/**
 * WAF ScanResult class
 */
class ScanResult implements ScanHttpResultInterface
{
    private $threats;

    private $vulnerabiltyScore = 0;

    private $messages;

    private $logs;

     /**
      * ScanHttpResultInterface constructor.
      * @param VulnerabilityInterface[] $threats
      */
    public function __construct(array $threats)
    {
        $this->threats = $threats;
        $this->vulnerabiltyScore = 0;
        $this->messages = [];
        foreach ($this->threats as $threat) {
            $this->vulnerabiltyScore += $threat->getScore();
            $this->messages[] = $threat->getMessage();
            $this->logs[] = $threat->getLogData();
        }
    }

    /**
     * @inheritDoc
     */
    public function getVulnerabilities()
    {
        return $this->threats;
    }

    /**
     * @inheritDoc
     */
    public function getVulnerabilityScore()
    {
        return $this->vulnerabiltyScore;
    }

    /**
     * @inheritDoc
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @inheritDoc
     */
    public function getLogs()
    {
        return $this->logs;
    }
}
