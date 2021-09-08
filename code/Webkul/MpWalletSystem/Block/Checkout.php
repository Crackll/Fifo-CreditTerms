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

namespace Webkul\MpWalletSystem\Block;

use Magento\Quote\Model\QuoteFactory;

/**
 * Webkul MpWalletSystem Block
 */
class Checkout extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Webkul\MpWalletSystem\Helper\Data
     */
    private $walletHelper;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;
    
    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\MpWalletSystem\Helper\Data               $walletHelper
     * @param \Magento\Checkout\Model\Session                  $checkoutSession
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpWalletSystem\Helper\Data $walletHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        QuoteFactory $quoteFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->walletHelper = $walletHelper;
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Get url on which ajax has been sent for setting wallet amount
     *
     * @return void
     */
    public function getAjaxUrl()
    {
        return $this->walletHelper->getAjaxUrl();
    }

    /**
     * Get grandtotal from quote data
     *
     * @return int
     */
    public function getGrandTotal()
    {
        $quote = '';
        if ($this->checkoutSession) {
            if ($this->checkoutSession->getQuoteId()) {
                $quoteId = $this->checkoutSession->getQuoteId();
                $quote = $this->quoteFactory->create()
                    ->load($quoteId);
            }
        }
        if ($quote) {
            $quoteData = $quote->getData();
            if (is_array($quoteData)) {
                if (array_key_exists('grand_total', $quoteData)) {
                    return $grandTotal = $quoteData['grand_total'];
                } else {
                    return 0;
                }
            }
        }
    }
}
