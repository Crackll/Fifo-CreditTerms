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
namespace Webkul\WebApplicationFirewall\Api;

interface ScanHttpResultInterface
{
    /**
     * Get Vulnerabilities
     *
     * @return \Webkul\WebApplicationFirewall\Api\Data\VulnerabilityInterface[]
     */
    public function getVulnerabilities();

    /**
     * Get Vulnerability Score
     *
     * @return int
     */
    public function getVulnerabilityScore();

    /**
     * Get messages
     *
     * @return array
     */
    public function getMessages();

    /**
     * Get logs
     *
     * @return array
     */
    public function getLogs();
}
