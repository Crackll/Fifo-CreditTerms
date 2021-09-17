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
namespace Webkul\MpPriceList\Plugin\Quote\Model\Quote;

use Magento\Quote\Model\Quote\TotalsCollector as Collector;

class TotalsCollector
{
    /**
     * @var \Webkul\PriceList\Helper\Data
     */
    private $_helper;

    /**
     * Initialize dependencies.
     *
     * @param \Webkul\MpPriceList\Helper\Data $helper
     */
    public function __construct(
        \Webkul\MpPriceList\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
        $this->_helper = $helper;
    }

    /**
     * around collect
     *
     * @param Collector $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote $quote
     * @return void
     */
    public function aroundCollect(
        Collector $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote $quote
    ) {
        $action = $this->request->getFullActionName();

        $data = $this->request->getParams();

        if(isset($data['uenc'])) {
            if ($this->_helper->isModuleEnabled() && $action != "sales_order_create_loadBlock" && $action != "sales_order_create_save") {
                $this->_helper->collectTotals($quote);
            }
        }
      
        return $proceed($quote);
    }
}
