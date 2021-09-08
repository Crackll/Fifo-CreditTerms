<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceProductLabels
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MarketplaceProductLabels\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * MarketplaceProductLabels data helper.
 */
class Data extends AbstractHelper
{
    const MARKETPLACE_ADMIN_NAME = "Admin";

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var LabelFactory
     */
    protected $labelFactory;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

   /**
    * @param \Magento\Framework\App\Helper\Context $context
    * @param \Magento\Store\Model\StoreManagerInterface $storeManager
    * @param \Magento\Catalog\Model\ProductFactory $productFactory
    * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
    * @param \Webkul\MarketplaceProductLabels\Model\LabelFactory $labelFactory
    * @param \Magento\Framework\Escaper $escaper
    */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Webkul\MarketplaceProductLabels\Model\LabelFactory $labelFactory,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->labelFactory = $labelFactory;
        $this->escaper = $escaper;
        parent::__construct($context);
    }

    /**
     * Return base url of media folder
     *
     * @return String
     */
    public function getMediaFolder()
    {
        $currentStore =  $this->storeManager->getStore();
        $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl;
    }

    /**
     * getProduct get the product model
     *
     * @param [int] $productId
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct($productId)
    {
        return $this->productFactory->create()->load($productId);
    }

    /**
     * Return the authorize seller status.
     *
     * @param string $labelId
     * @return boolean
     */
    public function isRightSeller($labelId = '')
    {
        $collection = $this->labelFactory->create()->getCollection()
            ->addFieldToFilter('id', $labelId)
            ->addFieldToFilter('seller_id', $this->getCustomerId());
        if ($collection->getSize()) {
            return true;
        }

        return false;
    }

    /**
     * Get Current Customer Id
     *
     * @return customer
     */
    public function getCustomerId()
    {
        return $this->customerSessionFactory->create()->getCustomerId();
    }

    /**
     * Get Product Label Configuration Data
     *
     * @param [type] $field
     * @return configData
     */
    public function getConfigData($field)
    {
        return $this->escaper->escapeHtml($this->scopeConfig->getValue(
            'mpproductlabel/general_settings/'.$field,
            ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * Get Allowed Image Extensions
     *
     * @return string
     */
    public function getAllowedImageExtensions()
    {
        $allowedExtensions = explode(",", $this->getConfigData('allowed_image_extensions'));
        return $allowedExtensions;
    }

    /**
     * Get Media Folder Url
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
    }

    /**
     * Get Admin Name for Email
     *
     * @return string
     */
    public function getAdminName()
    {
        $path = 'marketplace/general_settings/admin_name';
        $scope = ScopeInterface::SCOPE_STORE;
        $name = trim($this->scopeConfig->getValue($path, $scope));
        if ($name != "") {
            return $name;
        }

        return self::MARKETPLACE_ADMIN_NAME;
    }

    /**
     * Get Admin Email for Email
     *
     * @return string
     */
    public function getAdminEmailId()
    {
        return $this->scopeConfig->getValue(
            'marketplace/general_settings/adminemail',
            ScopeInterface::SCOPE_STORE
        );
    }
}
