<?php
/**
 * Webkul_MpDailyDeal.
 *
 * @category  Webkul
 * @package   Webkul_MpDailyDeal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpDailyDeal\Block\Account;

use Webkul\Marketplace\Helper\Data as MpHelper;

/**
 * MpDailyDeal Navigation link
 *
 */
class Navigation extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var MpHelper
     */
    protected $mpHelper;
    /**
     * @var helper
     */
    protected $helper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        MpHelper $mpHelper,
        \Webkul\MpDailyDeal\Helper\Data $helper,
        array $data = []
    ) {
        $this->mpHelper = $mpHelper;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return string current url
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     * getDealUrl for get complete url using url key
     * @param String $urlKey
     * @return String url
     */
    public function getDealUrl($urlKey)
    {
        return $this->getUrl(
            $urlKey,
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * getCurrentNavClass return nav item active or not class
     * @param  string $urlKey url key for match with current url
     * @return string|""
     */
    public function getCurrentNavClass($urlKey)
    {
        return strpos($this->getCurrentUrl(), $urlKey) ? "current" : "";
    }

    public function getDealHelper()
    {
        return $this->helper;
    }

    public function geMpHelper()
    {
        return $this->mpHelper;
    }
}
