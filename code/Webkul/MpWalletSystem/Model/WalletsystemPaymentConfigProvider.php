<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWalletSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWalletSystem\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;

/**
 * Webkul MpWalletSystem Model Class
 */
class WalletsystemPaymentConfigProvider implements WalletPaymentConfigProviderInterface
{
    /**
     * @var methodCode
     */
    protected $methodCode = PaymentMethod::CODE;
    
    /**
     * @var method
     */
    protected $method;
    
    /**
     * @var \Webkul\MpWalletSystem\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    
    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * Initialize dependencies
     *
     * @param PaymentHelper                      $paymentHelper
     * @param \Webkul\MpWalletSystem\Helper\Data $helper
     * @param \Magento\Framework\UrlInterface    $urlBuilder
     * @param Escaper                            $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        \Webkul\MpWalletSystem\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlBuilder,
        Escaper $escaper
    ) {
        $this->escaper = $escaper;
        $this->helper = $helper;
        $this->urlBuilder = $urlBuilder;
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
    }
        
    /**
     * Return Wallets COnfiguration
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        $image = '';
        $walletamount = '';
        $ajaxUrl = '';
        $walletstatus = '';
        $leftToPay = '';
        $leftinwallet = '';
        $currencysymbol = '';
        $getcurrentcode = '';
        $walletformatamount = '';
        $grandTotal = '';
        if ($this->method->isAvailable()) {
            $image = $this->getLoaderImage();
            $walletformatamount = $this->helper->getformattedPrice($this->getWalletamount());
            $walletamount = $this->getWalletamount();
            $ajaxUrl = $this->getAjaxUrl();
            $walletstatus = $this->getWalletStatus();
            $leftToPay = $this->getLeftToPay();
            $leftinwallet = $this->getLeftInWallet();
            $currencysymbol = $this->getCurrencySymbol();
            $getcurrentcode = $this->helper->getCurrentCurrencyCode();
            $grandTotal = $this->helper->getGrandTotal();
        }
        $config['mpwalletsystem']['loaderimage'] = $image;
        $config['mpwalletsystem']['getcurrentcode'] = $getcurrentcode;
        $config['mpwalletsystem']['walletformatamount'] = $walletformatamount;
        $config['mpwalletsystem']['walletamount'] = $walletamount;
        $config['mpwalletsystem']['ajaxurl'] = $ajaxUrl;
        $config['mpwalletsystem']['walletstatus'] = $walletstatus;
        $config['mpwalletsystem']['leftamount'] = $leftToPay;
        $config['mpwalletsystem']['leftinwallet'] = $leftinwallet;
        $config['mpwalletsystem']['currencysymbol'] = $currencysymbol;
        $config['mpwalletsystem']['grand_total'] = $grandTotal;

        return $config;
    }
        
    /**
     * Return Loader Image url
     *
     * @return string
     */
    protected function getLoaderImage()
    {
        return $this->method->getLoaderImage();
    }
    
    /**
     * Get Wallet Amount function
     *
     * @return int
     */
    protected function getWalletamount()
    {
        return $this->helper->getWalletTotalAmount(0);
    }
    
    /**
     * Get Ajax Url function
     *
     * @return string
     */
    protected function getAjaxUrl()
    {
        return $this->urlBuilder->getUrl('mpwalletsystem/index/applypaymentamount');
    }
    
    /**
     * Get Wallet Status function
     *
     * @return int
     */
    protected function getWalletStatus()
    {
        return $this->helper->getWalletStatus();
    }
    
    /**
     * Return Left Amount, other than wallet
     *
     * @return int
     */
    protected function getLeftToPay()
    {
        return $this->helper->getlefToPayAmount();
    }
    
    /**
     * Return Left Amount in wallet
     *
     * @return int
     */
    protected function getLeftInWallet()
    {
        return $this->helper->getLeftInWallet();
    }
    
    /**
     * Return Store Cureency Symbol
     *
     * @return character
     */
    protected function getCurrencySymbol()
    {
        return $this->helper->getCurrencySymbol($this->helper->getCurrentCurrencyCode());
    }
}
