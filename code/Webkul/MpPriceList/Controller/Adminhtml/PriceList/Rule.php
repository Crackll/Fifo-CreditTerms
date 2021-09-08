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
namespace Webkul\MpPriceList\Controller\Adminhtml\PriceList;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Rule extends \Magento\Backend\App\Action
{
    /**
     * @var \Webkul\MpPriceList\Model\PriceListFactory
     */
    protected $_priceList;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $_resultLayoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Webkul\MpPriceList\Model\PriceListFactory $priceList
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Webkul\MpPriceList\Model\PriceListFactory $priceList,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        $this->_priceList = $priceList;
        $this->_backendSession = $context->getSession();
        $this->_registry = $coreRegistry;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $priceList = $this->_priceList->create();
        if ($this->getRequest()->getParam('id')) {
            $priceList->load($this->getRequest()->getParam('id'));
        }
        $data = $this->_backendSession->getFormData(true);
        if (!empty($data)) {
            $priceList->setData($data);
        }
        $this->_registry->register('mppricelist_pricelist', $priceList);
        $resultLayout = $this->_resultLayoutFactory->create();
        return $resultLayout;
    }
}
