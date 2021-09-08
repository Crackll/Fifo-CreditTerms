<?php
/**
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Block\Adminhtml\Quotes\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Json\EncoderInterface;
use Magento\Backend\Model\Auth\Session;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quotes_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Manage Quotes Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab(
            "form_section",
            [
                "label"     =>  __("Quote Manager"),
                "alt"       =>  __("Quote Manager"),
                "content"   =>  $this->getLayout()
                ->createBlock(\Webkul\MpWholesale\Block\Adminhtml\EditQuotes::class)
                ->setTemplate("Webkul_MpWholesale::form.phtml")->toHtml()
            ]
        );
        $this->addTab(
            'conversation',
            [
                'label' => __('Quote Conversation'),
                'url'   => $this->getUrl(
                    'mpwholesale/*/grid',
                    ['_current' => true]
                ),
                'class' => 'ajax',
            ]
        );
        $this->addTab(
            'seller_details',
            [
                'label' => __('Seller Details'),
                "alt"       =>  __("Seller Details"),
                "content"   =>  $this->getLayout()
                ->createBlock(\Webkul\MpWholesale\Block\Adminhtml\SellerDetails::class)
                ->setTemplate("Webkul_MpWholesale::sellerDetails.phtml")->toHtml()
            ]
        );
        return parent::_beforeToHtml();
    }
}
