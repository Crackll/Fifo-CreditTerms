<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMSI
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpMSI\Plugin\Block\Product\Steps;

class Bulk
{
    /**
     * @var \Webkul\MpMSI\Helper\Data
     */
    protected $helper;

    /**
     * __construct
     *
     * @param \Webkul\MpMSI\Helper\Data $helper
     */
    public function __construct(
        \Webkul\MpMSI\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * do not overrite the file if single store mode is active
     *
     * @param string $template
     * @return void
     */
    public function aroundSetTemplate(
        \Webkul\Marketplace\Block\Product\Steps\Bulk $subject,
        \Closure $proceed,
        $template
    ) {
        if (!$this->helper->isSingleStoreMode()) {
            $template = "Webkul_MpMSI::catalog/product/edit/attributes/steps/bulk.phtml";
        } else {
            $template = "Webkul_Marketplace::product/edit/attribute/steps/bulk.phtml";
        }
        $proceed($template);
    }
}
