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

namespace Webkul\WebApplicationFirewall\Helper;

use Magento\Store\Model\Store;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Webkul\WebApplicationFirewall\Helper\Data as BaseHelper;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

/**
 * WAF module integration helper
 */
class Integration extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_PATH = 'waf_setting/';

    /** @var JsonSerializer */
    protected $serializer;

    /** @var \Webkul\WebApplicationFirewall\Logger\AbuseIPDBLogger */
    protected $_logger;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Webkul\WebApplicationFirewall\Helper\Data $helper,
        JsonSerializer $serializer
    ) {
        parent::__construct($context);
        $this->serializer = $serializer;
        $this->helper = $helper;
    }
    /**
     * get integration config data function
     *
     * @param string $field
     * @return string
     */
    public function getIntegrationData($group, $field)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH . $group . DIRECTORY_SEPARATOR . $field
        );
    }

    /**
     * Decode string from json to array
     *
     * @param string $jsonString
     * @return array
     * @throws \InvalidArgumentException
     */
    public function jsonDecode($jsonString)
    {
        try {
            return $this->serializer->unserialize($jsonString);
        } catch (\InvalidArgumentException $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * Retutn custom logger
     *
     * @return void
     */
    public function getAbuseIPDBLogger()
    {
        return $this->helper->getLogger('ip');
    }
}
