<?php
/**
 *
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPurchaseManagement\Block\Adminhtml\Order\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('purchase_order_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Purchase Order'));
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addTab(
            "order_info",
            [
                "label"     =>  __("Information"),
                "alt"       =>  __("Information"),
                "content"   =>  $this->getLayout()
                ->createBlock(\Webkul\MpPurchaseManagement\Block\Adminhtml\OrderView::class)
                ->setTemplate("Webkul_MpPurchaseManagement::view.phtml")->toHtml()
            ]
        );
        $this->addTab(
            'shipments',
            [
                'label' => __('Shipments'),
                'url'   => $this->getUrl(
                    'mppurchasemanagement/*/grid',
                    ['_current' => true]
                ),
                'class' => 'ajax',
            ]
        );
        return parent::_prepareLayout();
    }
}
