<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRewardSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpRewardSystem\Block\Account;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Managerewards extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $currency;
    /**
     * @var PriceCurrencyInterface
     */
    protected $jsonData;
    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;
    /**
     * @var Webkul\MpRewardSystem\Helper\Data
     */
    protected $rewardHelper;
    /**
     * @var Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;
    /**
     * @param Magento\Framework\View\Element\Template\Context $context
     * @param Magento\Framework\ObjectManagerInterface $objectManager
     * @param Magento\Customer\Model\Session $customerSession
     * @param Magento\Framework\Json\Helper\Data $jsonData
     * @param Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param Webkul\MpRewardSystem\Helper\Data $rewardHelper
     * @param PriceCurrencyInterface $currency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Json\Helper\Data $jsonData,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Webkul\MpRewardSystem\Helper\Data $rewardHelper,
        PriceCurrencyInterface $currency,
        array $data = []
    ) {
        $this->objectManager = $objectManager;
        $this->customerSession = $customerSession;
        $this->jsonData = $jsonData;
        $this->currency = $currency;
        $this->rewardHelper = $rewardHelper;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->pricingHelper = $pricingHelper;
        parent::__construct($context, $data);
    }
    /**
     * @return customer Session
     */
    public function _getCustomerData()
    {
        return $this->customerSession->getCustomer();
    }
    /**
     * @return currency symbol
     */
    public function getCurrentCurrencySymbol()
    {
        return $this->currency->getCurrencySymbol();
    }
    /**
     * @return json Data
     */
    public function getJsonData()
    {
        return $this->jsonData;
    }
    /**
     * @return marketplace helper
     */
    public function getMarketplaceHelper()
    {
        return $this->marketplaceHelper;
    }
    /**
     * @return reward helper
     */
    public function getRewardHelper()
    {
        return $this->rewardHelper;
    }
    /**
     * @return princing helper
     */
    public function getPricingHelper()
    {
        return $this->pricingHelper;
    }
}
