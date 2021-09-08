<?php
/**
 * Webkul MpAuction AddAuction Controller
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Controller\Adminhtml\Auction;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action as Act;
use Webkul\MpAuction\Model\ProductFactory as AuctionProductFactory;

class Disable extends Act
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var Webkul\MpAuction\Model\ProductFactory
     */
    private $auctionProduct;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry         $coreRegistry
     * @param \Magento\Catalog\Model\Product      $product
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\Product $product,
        AuctionProductFactory $auctionProduct,
        \Magento\Framework\Json\Helper\Data $jsonData
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->product = $product;
        $this->auctionProduct = $auctionProduct;
        $this->_jsonData = $jsonData;
    }
    /**
     * Add New MpAuction Form page.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $disabled=false;
        $productId = $this->getRequest()->getParam('option');
        $product = $this->product->load($productId);
        $response=$this->getResponse()->setHeader('Content-type', 'application/javascript');
        if ($product->getTypeId()=="downloadable") {
            $disabled=true;
            $this->getResponse()->setBody($this->_jsonData
                ->jsonEncode(
                    [
                    'disabled' =>$disabled
                    ]
                ));
        } else {
            $disabled=false;
            $this->getResponse()->setBody($this->_jsonData
                ->jsonEncode(
                    [
                    'disabled' =>$disabled
                    ]
                ));
        }
    }
}
