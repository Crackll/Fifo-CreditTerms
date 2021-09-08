<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Controller\Adminhtml\Product;

class Grid extends \Magento\Backend\App\Action
{
    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @param \Magento\Framework\View\LayoutFactory             $layoutFactory
     * @param \Magento\Framework\Registry                       $coreRegistry
     * @param \Magento\Backend\App\Action\Context               $context
     * @param \Magento\Framework\Controller\Result\RawFactory   $resultRawFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {

        parent::__construct($context);

        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Grid Action.
     * Display list of products related to current customer.
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {

        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                \Webkul\MpWholesale\Block\Adminhtml\Options\Tab\Product::class,
                'template.product.grid'
            )->toHtml()
        );
    }
}
