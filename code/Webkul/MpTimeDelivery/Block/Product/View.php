<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpTimeDelivery
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpTimeDelivery\Block\Product;

use Webkul\Marketplace\Helper\Data as MpHelper;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\ProductFactory;
use Webkul\MpTimeDelivery\Helper\Data as Helper;

/**
 * Block for product view page quote button
 */
class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var MpHelper
     */
    protected $mpHelper;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param Context $context
     * @param ProductFactory $productFactory
     * @param MpHelper $mpHelper
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        MpHelper $mpHelper,
        Helper $helper,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->mpHelper = $mpHelper;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function blockCanShow()
    {
        $id = $this->getRequest()->getParam('id');
        $product = $this->productFactory->create()->load($id);
        $stockStatus = $product->getQuantityAndStockStatus()['is_in_stock'];
        $productType = $product->getTypeId();
        $isPreorder = $this->helper->isPreOrder($id);
        if ((
            $productType == 'simple' || $productType == 'configurable' ||
            $productType == 'bundle' || $productType == 'grouped') && !$isPreorder && $stockStatus) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get SellerId
     *
     * @return int
     */
    public function getSellerId()
    {
        $id = $this->getRequest()->getParam('id');
        $sellerData = $this->mpHelper->getSellerProductDataByProductId($id)->getData();
        $sellerId = 0;
        foreach ($sellerData as $key => $data) {
            $sellerId = ($data['seller_id']);
        }
        return $sellerId;
    }
}
