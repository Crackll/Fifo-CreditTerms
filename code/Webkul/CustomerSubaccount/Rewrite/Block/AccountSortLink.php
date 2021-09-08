<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_CustomerSubaccount
 * @author     Webkul
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\CustomerSubaccount\Rewrite\Block;

use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class AccountSortLink extends \Magento\Customer\Block\Account\SortLink
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
     * To HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getPath() == 'review/customer' && !$this->helper->canReviewProducts()) {
            return '';
        }
        return parent::_toHtml();
    }
}
