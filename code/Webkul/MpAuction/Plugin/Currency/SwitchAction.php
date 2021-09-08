<?php

/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpAuction
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MpAuction\Plugin\Currency;

use Webkul\MpAuction\Logger\Logger;
use Webkul\MpAuction\Helper\Data;

class SwitchAction
{

    /**
     * @var Logger
     */
    public $logger;

    /**
     * @var Data
     */
    public $helper;

    /**
     * @var Session
     */
    public $checkoutSession;

    /**
     * @var Request
     */
    public $request;

    public $rate = [];

    /**
     * @param Logger $logger
     * @param  \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Webapi\Rest\Request $request,
     */
    public function __construct(
        Logger $logger,
        Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Webapi\Rest\Request $request
    ) {
        $this->logger =  $logger;
        $this->helper = $helper;
        $this->checkoutSession =  $checkoutSession;
        $this->request =  $request;
    }
    /**
     * beforeExecute
     * @param  \Magento\Directory\Controller\Currency\SwitchAction $subject
     */
    public function beforeExecute(
        \Magento\Directory\Controller\Currency\SwitchAction $subject
    ) {
        $fromCurrencyCode = $this->helper->getCurrentCurrencyCode();
        $tocurrencyCode =  (string) $this->request->getParam('currency');
        $quote = $this->checkoutSession->getQuote()->getAllVisibleItems();
        foreach ($quote as $item) {
            $customPrice = $item->getOriginalCustomPrice();
            $customPrice = $this->helper->getwkconvertCurrency($fromCurrencyCode, $tocurrencyCode, $customPrice);
            $this->rate[$item->getId()] = $customPrice;
        }
    }
    /**
     * afterExecute
     * @param  \Magento\Directory\Controller\Currency\SwitchAction $subject
     */
    public function afterExecute(
        \Magento\Directory\Controller\Currency\SwitchAction $subject
    ) {
        $quote = $this->checkoutSession->getQuote()->getAllVisibleItems();
        foreach ($quote as $item) {
            if (!empty($item->getOriginalCustomPrice())) {
                $item->setOriginalCustomPrice($this->rate[$item->getId()]);
                $item->save();
            }
        }
    }
}
