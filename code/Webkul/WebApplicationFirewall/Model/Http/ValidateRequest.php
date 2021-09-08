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

use Webkul\WebApplicationFirewall\Api\ValidateHttpRequestInterface;
use Magento\Framework\App\RequestInterface;
use Webkul\WebApplicationFirewall\Model\Validators\IpValidatorInterface;
use Webkul\WebApplicationFirewall\Integrations\ValidatorInterface as IntegrationValidator;
use Webkul\WebApplicationFirewall\Integrations\AbuseIPDBInterface;
use Webkul\WebApplicationFirewall\Api\ScanHttpResultInterfaceFactory;
use Webkul\WebApplicationFirewall\Api\Data\VulnerabilityInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

/**
 * ValidateRequest validate HTTP request class
 */
class ValidateRequest implements ValidateHttpRequestInterface
{
    /**
     * @var $remoteAddress
     */
    protected $remoteAddress;

    public function __construct(
        RequestInterface $request,
        IpValidatorInterface $ipValidator,
        IntegrationValidator $integrationValidator,
        ScanHttpResultInterfaceFactory $scanHttpResultFactory,
        \Webkul\WebApplicationFirewall\Helper\Data $helper,
        RemoteAddress $remoteAddress,
        $integrations = []
    ) {
        $this->request = $request;
        $this->ipValidator = $ipValidator;
        $this->integrationValidator = $integrationValidator;
        $this->scanHttpResultFactory = $scanHttpResultFactory;
        $this->helper = $helper;
        $this->integrations = $integrations;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * @inheritDoc
     */
    public function scanHttpRequest()
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function scanForBlockedUsers()
    {
        $enabledCountryBan = $this->helper->getConfigData('country_ban', 'enable');
        $results = [];

        $results[] = $this->validateIp();

        if ($enabledCountryBan) {
            $results[] = $this->validateCountries();
        }

        $responses = $this->validateByIntegration();
        foreach ($responses as $response) {
            $results[] = $response;
        }
        $scanHttpResult = $this->scanHttpResultFactory->create(['threats' =>  $results]);

        return $scanHttpResult;
    }

    /**
     * Validate request ip
     *
     * @return VulnerabilityInterface
     */
    public function validateIp()
    {
        $request = $this->request;

        $forwardedIp = $request->getHeader('x-forwarded-for');
        $remoteIp = $this->remoteAddress->getRemoteAddress();

        $requestIps = array_filter(array_map('trim', explode(',', $request->getClientIp())));
        $forwardedIps = array_filter(array_map('trim', explode(',', $forwardedIp)));
        $remoteIps = array_filter(array_map('trim', explode(',', $remoteIp)));

        $this->ipValidator->setClientIps($requestIps);
        $this->ipValidator->setForwardedIps($forwardedIps);
        $this->ipValidator->setRemoteIps($remoteIps);

        $response = $this->ipValidator->validate();
        if ($response->getScore()) {
            if ($request->getPostValue('login')) {
                $request->setPostValue('login', null);
            }
        }
        return $response;
    }

    /**
     * Validate for alloed countries
     *
     * @return VulnerabilityInterface
     */
    public function validateCountries()
    {
        $request = $this->request;
        $requestIps = array_filter(array_map('trim', explode(',', $request->getClientIp())));
        $this->ipValidator->setClientIps($requestIps);
        $response = $this->ipValidator->validateCountry();
        if ($response->getScore()) {
            if ($request->getPostValue('login')) {
                $request->setPostValue('login', null);
            }
        }
        return $response;
    }

    /**
     * Validate client by integrations
     *
     * @param string $request
     * @return VulnerabilityInterface[]
     */
    private function validateByIntegration($type = '')
    {
        $request = $this->request;
        $requestIps = array_filter(array_map('trim', explode(',', $request->getClientIp())));
        $responses= [];
        $this->integrationValidator->setClientIps($requestIps);
        foreach ($this->integrations as $type) {
            $this->integrationValidator->setType($type);
            $responses[] = $this->integrationValidator->validate();
        }

        return $responses;
    }
}
