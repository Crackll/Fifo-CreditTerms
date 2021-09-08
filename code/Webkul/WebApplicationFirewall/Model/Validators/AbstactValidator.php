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

use Webkul\WebApplicationFirewall\Integrations\AbuseIPDBInterface;
use Webkul\WebApplicationFirewall\Api\Data\VulnerabilityInterfaceFactory;

/**
 * abstract validator model
 */
abstract class AbstactValidator extends \Magento\Framework\DataObject
{
    /**
     * @var \Webkul\WebApplicationFirewall\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\WebApplicationFirewall\Helper\Integration
     */
    protected $integrationHelper;

    /**
     * @var string
     */
    protected $validationType = null;

    /**
     * @var AbuseIPDBInterface
     */
    protected $abuseIPDB;

    protected $vulnerabilityFactory;

    /**
     * @param \Webkul\WebApplicationFirewall\Helper\Data $helper
     */
    public function __construct(
        \Webkul\WebApplicationFirewall\Helper\Data $helper,
        \Webkul\WebApplicationFirewall\Helper\Integration $integrationHelper,
        AbuseIPDBInterface $abuseIPDB,
        VulnerabilityInterfaceFactory $vulnerabilityFactory
    ) {
        $this->helper = $helper;
        $this->integrationHelper = $integrationHelper;
        $this->abuseIPDB = $abuseIPDB;
        $this->vulnerabilityFactory = $vulnerabilityFactory;
    }

    abstract public function validate();
}
