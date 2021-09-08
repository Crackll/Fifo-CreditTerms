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
namespace Webkul\CustomerSubaccount\Block\Subaccount;

class Edit extends \Magento\Framework\View\Element\Template
{
    /**
     * Context
     *
     * @var \Magento\Framework\View\Element\Template\Context
     */
    public $context;

    /**
     * Subaccount Model
     *
     * @var \Webkul\CustomerSubaccount\Model\SubaccountFactory
     */
    public $subaccountFactory;

    /**
     * Helper
     *
     * @var \Webkul\CustomerSubaccount\Helper\Data
     */
    public $helper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\CustomerSubaccount\Model\SubaccountFactory $subaccountFactory
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\CustomerSubaccount\Model\SubaccountFactory $subaccountFactory,
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->subaccountFactory = $subaccountFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get Subaccound
     *
     * @param int $id
     * @return \Webkul\CustomerSubaccount\Model\Subaccount
     */
    public function getSubAccount($id)
    {
        return $this->subaccountFactory->create()->load($id);
    }

    /**
     * Get Helper
     *
     * @return object
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
