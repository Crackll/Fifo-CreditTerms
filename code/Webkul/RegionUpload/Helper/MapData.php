<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RegionUpload
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\RegionUpload\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class MapData extends \Magento\Framework\App\Helper\AbstractHelper implements ArgumentInterface
{
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $context;
       
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var \Magento\Customer\Helper\Address
     */
    protected $customerHelper;
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * Constructor.
     *
     * @param \Magento\Framework\App\Helper\Context                $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface   $scopeConfig
     * @param \Magento\Directory\Helper\Data                       $directoryHelper
     * @param \Magento\Customer\Helper\Address                     $customerHelper
     * @param \Magento\Framework\Encryption\EncryptorInterface     $encryptor
     */

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Customer\Helper\Address $customerHelper,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->directoryHelper = $directoryHelper;
        $this->customerHelper = $customerHelper;
        $this->_encryptor = $encryptor;
        parent::__construct($context);
    }
    /**
     * Get Api Key
     *
     * @return string $apiKey
     */

    public function getApiKey()
    {
        $apiKey = $this->_encryptor->decrypt(
            $this->scopeConfig->getValue(
                'wk_region_upload/wk_regionupload_enable_google_map_pin/wk_regionupload_api_key'
            )
        );
        return $apiKey;
    }

    /**
     * Get Google Map Status
     *
     * @return integer $status
     */
    public function getGoogleMapStatus()
    {
        $status = $this->scopeConfig->getValue(
            'wk_region_upload/wk_regionupload_enable_google_map_pin/wk_regionupload_enable_google_pin'
        );
        return $status;
    }
    /**
     * Get Directory Data
     *
     * @return object
     */
    public function getDirectoryData()
    {
        return $this->directoryHelper;
    }

    /**
     * Get Customer Helper Address
     *
     * @return object
     */
    public function getCustomerHelAdd()
    {
        return $this->customerHelper;
    }
}
