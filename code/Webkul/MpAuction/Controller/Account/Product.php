<?php
/**
 * Webkul MpAuction detail controller.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\MpAuction\Helper\Data;

class Product extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $_resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $_resultPageFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        Data $helperData
    ) {
        $this->_resultPageFactory = $_resultPageFactory;
        parent::__construct($context);
        $this->_cacheTypeList = $cacheTypeList;
        $this->helperData = $helperData;
        $this->_cacheFrontendPool = $cacheFrontendPool;
    }

    /**
     * Auction Detail page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $product_id = $this->getRequest()->getParam('productId');
        $this->helperData->cleanByTags($product_id);
        return $resultPage;
    }
}
