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

interface AbuseIPDBInterface
{
    const CODE = 'abuseipdb';

    const SERVER_URL = 'https://api.abuseipdb.com/api/v2/';

    /**
     * Get ip details
     * @param string $ip
     * @return array|null
     * @throws \Exception
     */
    public function checkIp($ip);

    /**
     * Get blacklisted ips from AbuseIPDB
     * @return array
     * @throws \Exception
     */
    public function getBlackListedIps();

    /**
     * Report IP on AbuseIPDB
     *
     * @param string $ip
     * @return bool
     * @throws \Exception
     */
    public function reportIp($ip, $categories = []);

    /**
     * Bulk Report IP on AbuseIPDB
     *
     * @param string $ip
     * @return bool
     * @throws \Exception
     */
    public function bulkReport($ip);
}
