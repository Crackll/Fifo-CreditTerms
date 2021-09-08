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
namespace Webkul\MpPriceList\ViewModel;

class ViewModel implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    private $helperData;
    public function __construct(
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MpPriceList\Helper\Data $priceListHelper
    ) {
        $this->mpHelper = $mpHelper;
        $this->priceListHelper = $priceListHelper;
    }
    public function getMpHelper()
    {
        return $this->mpHelper;
    }
    public function getPriceListHelper()
    {
        return $this->priceListHelper;
    }
}
