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
namespace Webkul\MpWholesale\Block\Quotation;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\ProductFactory;
use Webkul\Marketplace\Helper\Data;
use Webkul\MpWholesale\Helper\Data as MpWholesaleHelper;
use Webkul\MpWholesale\Model\QuoteconversationFactory;
use Webkul\MpWholesale\Model\QuotesFactory;

class Edit extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;
    
    /**
     * @var \Webkul\MpWholesale\Helper\Data
     */
    protected $mpWholesaleHelper;
    
    /**
     * @var \Webkul\MpWholesale\Model\QuoteconversationFactory
     */
    protected $quotesFactory;
    
    /**
     * @var \Webkul\MpWholesale\Model\QuotesFactory
     */
    protected $quoteconversationFactory;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;
    
    /**
     * @var quoteCollection
     */
    protected $_quoteConversationCollection;
    
    /**
     * @param Context $context
     * @param ProductFactory $productFactory
     * @param Data $mpHelper
     * @param MpWholesaleHelper $mpWholesaleHelper
     * @param QuotesFactory $quotesFactory
     * @param QuoteconversationFactory $quoteconversationFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        Data $mpHelper,
        MpWholesaleHelper $mpWholesaleHelper,
        QuotesFactory $quotesFactory,
        QuoteconversationFactory $quoteconversationFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->imageHelper = $context->getImageHelper();
        $this->mpHelper = $mpHelper;
        $this->mpWholesaleHelper = $mpWholesaleHelper;
        $this->quotesFactory = $quotesFactory;
        $this->quoteconversationFactory = $quoteconversationFactory;
        $this->productFactory = $productFactory;
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getQuoteConversationCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'quotation.pager'
            )
            ->setCollection(
                $this->getQuoteConversationCollection()
            );
            $this->setChild('pager', $pager);
            $this->getQuoteConversationCollection()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    /**
     * get Collection of quote conversation for particular quote id.
     *
     * @return collection
     */
    public function getQuoteConversationCollection()
    {
        if (!$this->_quoteConversationCollection) {
            $quoteId = $this->getRequest()->getParam('id');
            $collection = $this->quoteconversationFactory->create()
                        ->getCollection()
                        ->addFieldToFilter(
                            'quote_id',
                            $quoteId
                        )
                        ->setOrder('entity_id', 'DESC');
            $this->_quoteConversationCollection = $collection;
        }
        return $this->_quoteConversationCollection;
    }

    //get quote object by quoteId
    public function getQuoteData($entityId)
    {
        $quoteModel = $this->quotesFactory->create()->load($entityId);
        return $quoteModel;
    }

    public function getProductData($productId)
    {
        $productModel = $this->productFactory->create()->load($productId);
        return $productModel;
    }
    
    public function imageHelperObj()
    {
        return $this->imageHelper;
    }
    
    /**
     * Return Marketplace Helper Object
     *
     * @return \Webkul\Marketplace\Helper\Data
     */
    public function getMpHelper()
    {
        return $this->mpHelper;
    }
    
    /**
     * Return MpWholesale Helper Object
     *
     * @return \Webkul\MpWholesale\Helper\Data
     */
    public function getWholeSaleHelper()
    {
        return $this->mpWholesaleHelper;
    }
}
