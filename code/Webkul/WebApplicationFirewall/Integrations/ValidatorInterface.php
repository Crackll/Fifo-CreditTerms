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

interface ValidatorInterface
{
    const CLIENT_IPS = 'client_ips';
    const REMOTE_IPS = 'remote_ips';
    const CODE = '';

    /**
     * Set integration code
     *
     * @param string $ips
     * @return $this
     */
    public function setType($code);

    /**
     * Get integration code
     *
     * @return string|null
     */
    public function getType();

    /**
     * Set request client ips
     *
     * @param mixed $ips
     * @return $this
     */
    public function setClientIps($ips);

    /**
     * Get request client ips
     *
     * @return mixed|null
     */
    public function getClientIps();

    /**
     * Set remote client ips
     *
     * @param mixed $ips
     * @return $this
     */
    public function setRemoteIps($ips);

    /**
     * Set remote client ips
     *
     * @return mixed|null
     */
    public function getRemoteIps();
}
