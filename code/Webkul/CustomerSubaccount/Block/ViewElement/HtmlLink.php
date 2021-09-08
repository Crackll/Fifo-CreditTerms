<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomerSubaccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\CustomerSubaccount\Block\ViewElement;

use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class HtmlLink extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * Context
     *
     * @var \Magento\Framework\View\Element\Template\Context
     */
    public $context;

    /**
     * Default Path
     *
     * @var \Magento\Framework\App\DefaultPathInterface
     */
    public $defaultPath;

    /**
     * Helper
     *
     * @var \Webkul\CustomerSubaccount\Helper\Data
     */
    public $helper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param DefaultPathInterface $defaultPath
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->helper = $helper;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $customerId = $this->helper->getCustomerId();
        if ($this->getPath() == 'wkcs/subaccount' && !$this->helper->canCreateSubaccounts($customerId)) {
            return '';
        } elseif ($this->getPath() == 'wkcs/approveCarts' && !$this->helper->canApproveCarts($customerId)) {
            return '';
        } elseif ($this->getPath() == 'wkcs/mergeCarts' && $this->helper->isSubaccountUser($customerId)) {
            return '';
        } elseif ($this->getPath() == 'wkcs/myCarts'
            && (!$this->helper->isSubaccountUser($customerId)
                || (!$this->helper->canMergeCartToMainCart($customerId)
                    && !$this->helper->isCartApprovalRequired($customerId))
            )
        ) {
            return '';
        } elseif ($this->getPath() == 'wkcs/wishList'
            && (!$this->helper->canViewMainWishlist($customerId)
                || !$this->helper->isSubaccountUser($customerId))) {
            return '';
        } elseif ($this->getPath() == 'wkcs/mainOrder'
            && (!$this->helper->canViewMainAccountOrderList($customerId)
                || !$this->helper->isSubaccountUser($customerId))) {
            return '';
        } elseif ($this->getPath() == 'wkcs/subaccountOrder'
                    && !$this->helper->canViewSubAccountOrderList($customerId)) {
            return '';
        } else {
            if (false != $this->getTemplate()) {
                return parent::_toHtml();
            }
    
            $highlight = '';
    
            if ($this->getIsHighlighted()) {
                $highlight = ' current';
            }
    
            if ($this->isCurrent()) {
                $html = '<li class="wkcs-nav-li nav item current">';
                $html .= '<strong>'
                    . $this->escapeHtml(__($this->getLabel()))
                    . '</strong>';
                $html .= '</li>';
            } else {
                $html = '<li class="wkcs-nav-li nav item' . $highlight . '"><a href="'
                        . $this->escapeHtml($this->getHref()) . '"';
                $html .= $this->getTitle()
                    ? ' title="' . $this->escapeHtml(__($this->getTitle())) . '"'
                    : '';
                $html .= $this->getAttributesHtml() . '>';
    
                if ($this->getIsHighlighted()) {
                    $html .= '<strong>';
                }
    
                $html .= $this->escapeHtml(__($this->getLabel()));
    
                if ($this->getIsHighlighted()) {
                    $html .= '</strong>';
                }
    
                $html .= '</a></li>';
            }
    
            return $html;
        }
    }
}
