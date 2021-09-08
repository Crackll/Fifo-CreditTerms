<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPurchaseManagement\Block;

class QuotationEdit extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\MpWholesale\Model\QuotesFactory
     */
    protected $quotesFactory;

    /**
     * @var \Webkul\MpPurchaseManagement\Model\OrderItemFactory
     */
    protected $orderItemFactory;

   /**
    * @param Context        $context
    * @param QuotesFactory  $quotesFactory
    * @param array          $data
    */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Webkul\MpWholesale\Model\QuotesFactory $quotesFactory,
        \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory,
        array $data = []
    ) {
        $this->quotesFactory = $quotesFactory;
        $this->orderItemFactory = $orderItemFactory;
        parent::__construct($context, $data);
    }

    /**
     * show create purchase order button or not
     * @return bool
     */
    public function isShowButton()
    {
        $quote = $this->quotesFactory->create()->load($this->getQuoteId());
        $collection = $this->orderItemFactory->create()->getCollection()
                           ->addFieldToFilter('quote_id', ['eq'=>$this->getQuoteId()]);
        if ($quote->getStatus()==\Webkul\MpWholesale\Model\Quotes::STATUS_APPROVED && $collection->getSize()==0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get create purchase order url
     * @return string
     */
    public function getOrderUrl()
    {
        return $this->getUrl(
            'mppurchasemanagement/order/create',
            ['_secure' => $this->getRequest()->isSecure(),'quote_id' => $this->getQuoteId()]
        );
    }

    /**
     * get quote ID
     * @return int
     */
    public function getQuoteId()
    {
        return $this->getRequest()->getParam('id');
    }
}
