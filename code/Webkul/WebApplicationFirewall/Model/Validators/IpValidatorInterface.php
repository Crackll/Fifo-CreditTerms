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

 namespace Webkul\WebApplicationFirewall\Model\Validators;

interface IpValidatorInterface
{
    /**
     * Set request client ips
     *
     * @param mixed $ips
     * @return $this
     */
    public function setClientIps($ips);

    /**
     * Set request client ips
     *
     * @return mixed|null
     */
    public function getClientIps();

    /**
     * Set forwarded client ips
     *
     * @param mixed $ips
     * @return $this
     */
    public function setForwardedIps($ips);

    /**
     * Set forwarded client ips
     *
     * @return mixed|null
     */
    public function getForwardedIps();

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
