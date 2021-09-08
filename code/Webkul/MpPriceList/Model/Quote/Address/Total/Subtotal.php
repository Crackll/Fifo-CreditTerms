<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPriceList
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPriceList\Model\Quote\Address\Total;

class Subtotal extends \Magento\Quote\Model\Quote\Address\Total\Subtotal
{
    /**
     * @param \Webkul\MpPriceList\Helper\Data $helper
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Quote\Model\QuoteValidator $quoteValidator
     */
    public function __construct(
        \Webkul\MpPriceList\Helper\Data $helper,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Model\QuoteValidator $quoteValidator
    ) {
        $this->_helper = $helper;
        $this->quoteFactory = $quoteFactory;
        parent::__construct($quoteValidator);
    }

    /**
     * Processing calculation of row price for address item
     *
     * @param AddressItem|Item $item
     * @param int $finalPrice
     * @param int $originalPrice
     * @return $this
     */
    protected function _calculateRowTotal(
        $item,
        $finalPrice,
        $originalPrice
    ) {
        $quote = '';
        if (!empty($item)) {
            if (!empty($item->getQuoteItem())) {
                $quote = $this->quoteFactory->create()->load($item->getQuoteItem()->getQuoteId());
            } elseif (!empty($item->getQuoteItem())) {
                $quote = $this->quoteFactory->create()->load($item->getQuoteId());
            }
        }
        if (($this->_helper->isModuleEnabled()) && !empty($quote) && ($quote->getIsMultiShipping() == 1)) {
            $finalPrice = $item->getPrice();
            $originalPrice = $item->getPrice();
            $item->setPrice($finalPrice)->setBaseOriginalPrice($originalPrice);
            $item->calcRowTotal();
            return $this;
        } else {
            return parent::_calculateRowTotal($item, $finalPrice, $originalPrice);
        }
    }
}
