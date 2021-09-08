<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerCategory
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerCategory\Block;

use Magento\Framework\View\Element\Html\Link\Current;

class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;

    /**
     * @var \Webkul\MpSellerCategory\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\MpSellerCategory\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MpSellerCategory\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath);
        $this->_mpHelper = $mpHelper;
        $this->_helper = $helper;
    }

    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_mpHelper->isSeller()) {
            return '';
        }

        if (!$this->_helper->isAllowedSellerCategories()) {
            return '';
        }

        if (!$this->_helper->isAllowedSellerToManageCategories()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Get Current Url
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     * Get Marketplace helper data
     *
     * @return object
     */
    public function getMpHelper()
    {
        return $this->_mpHelper;
    }
}
