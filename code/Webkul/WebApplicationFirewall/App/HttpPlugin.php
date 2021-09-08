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
namespace Webkul\WebApplicationFirewall\App;

use Magento\Framework\App\State;
use Magento\Framework\AppInterface;
use Magento\Framework\App\RequestInterface;
use Webkul\WebApplicationFirewall\Api\ValidateHttpRequestInterface;
use Webkul\WebApplicationFirewall\Api\ScanHttpResultInterface;

/**
 * WAF HttpPlugin class
 */
class HttpPlugin
{
    public function __construct(
        RequestInterface $request,
        State $state,
        ValidateHttpRequestInterface $validateHttpRequest,
        \Webkul\WebApplicationFirewall\Helper\Data $helper,
        \Webkul\WebApplicationFirewall\Helper\ErrorProcessor $errorProcessor
    ) {
        $this->request = $request;
        $this->state = $state;
        $this->validateHttpRequest = $validateHttpRequest;
        $this->helper = $helper;
        $this->errorProcessor = $errorProcessor;
    }

    /**
     * Check for blocked ips and attacks
     *
     * @param \Magento\Framework\AppInterface $subject
     * @param \Closure $proceed
     * @return \Magento\Framework\App\Response\Http|mixed
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     * @SuppressWarnings("PHPMD.CyclomaticComplexity")
     */
    public function aroundLaunch(\Magento\Framework\AppInterface $subject, \Closure $proceed)
    {
        if ($this->helper->isEnabled()) {
            $minimupIpScoreToStop = $this->helper->getConfigData(
                'abuseipdb',
                'abuse_score'
            );

            $ipDebugEnabled = $minimupIpScoreToStop = $this->helper->getConfigData(
                'ips_ban',
                'enable_log'
            );
            $response = $this->validateHttpRequest->scanForBlockedUsers();
            if ($ipDebugEnabled) {
                $this->helper->getDebug()
                    ->processDebuging($response->getLogs(), 'ip', 'info');
            }
            if ($response->getVulnerabilityScore() > 80 ||
                ($minimupIpScoreToStop && $minimupIpScoreToStop <=  $response->getVulnerabilityScore())
            ) {
                $this->state->setAreaCode('frontend');
                return $this->errorProcessor->processIpBlockError($response);
            }

        }
        return $proceed();
    }
}
