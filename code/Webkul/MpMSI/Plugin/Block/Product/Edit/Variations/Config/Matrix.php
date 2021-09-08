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

namespace Webkul\MpMSI\Plugin\Block\Product\Edit\Variations\Config;

class Matrix
{

    /**
     * @var \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku
     */
    protected $sourceDataBySku;

    /**
     * @var \Webkul\MpMSI\Helper\Data
     */
    protected $helper;

    public function __construct(
        \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku $sourceDataBySku,
        \Webkul\MpMSI\Helper\Data $helper
    ) {
        $this->sourceDataBySku = $sourceDataBySku;
        $this->helper = $helper;
    }

    /**
     * for multi source mode add quantity per source data
     *
     * @param \Webkul\Marketplace\Block\Product\Edit\Variations\Config\Matrix $subject
     * @param  $result
     * @return []
     */
    public function afterGetSellerProductMatrix(
        \Webkul\Marketplace\Block\Product\Edit\Variations\Config\Matrix $subject,
        $result
    ) {
        if ($result && count($result) > 0) {
            foreach ($result as &$data) {
                $stocks = $this->sourceDataBySku->execute($data['sku']);
                $data["quantity_per_source"] = $stocks;
            }
            
            return $result;
        }
    }

    /**
     * do not overrite the file if single store mode is active
     *
     * @param string $template
     * @return void
     */
    public function aroundSetTemplate(
        \Webkul\Marketplace\Block\Product\Edit\Variations\Config\Matrix $subject,
        \Closure $proceed,
        $template
    ) {
        if (!$this->helper->isSingleStoreMode()) {
            $template = "Webkul_MpMSI::catalog/product/edit/super/matrix.phtml";
        } else {
            $template = "Webkul_Marketplace::product/edit/super/matrix.phtml";
        }
        $proceed($template);
    }
}
