<?php
/**
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Block\Adminhtml;

use Webkul\MpWholesale\Model\ResourceModel\Quoteconversation;
use Magento\Framework\Pricing\Helper\Data;
use Webkul\MpWholesale\Model\QuotesFactory;
use Webkul\MpWholesale\Model\QuoteconversationFactory;
use Magento\Catalog\Model\ProductFactory;
use Webkul\MpWholesale\Api\QuoteRepositoryInterface;

class EditQuotes extends \Magento\Framework\View\Element\Template
{
    /**
     * @var customerSession
     */
    protected $customerSession;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerModel;
    /**
     * @var quoteCollection
     */
    protected $quoteConversationCollection;
    /**
     * @var pricingHelper
     */
    protected $pricingHelper;
    /**
     * @var QuotesFactory
     */
    protected $quotesFactory;
    /**
     * @var ProductFactory
     */
    protected $productFactory;
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;
    /**
     * @var QuoteconversationFactory
     */
    protected $quoteConversationCollectionFactory;
    /**
     * @var Webkul\Quotesystem\Api\QuoteRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Customer $customerModel
     * @param Quoteconversation\CollectionFactory $conversationCollectionFactory
     * @param QuotesFactory $quotesFactory
     * @param ProductFactory $productFactory
     * @param Data $pricingHelper
     * @param QuoteRepositoryInterface $quoteRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Customer $customerModel,
        Quoteconversation\CollectionFactory $conversationCollectionFactory,
        QuotesFactory $quotesFactory,
        ProductFactory $productFactory,
        Data $pricingHelper,
        QuoteRepositoryInterface $quoteRepository,
        \Webkul\MpWholesale\Helper\Data $wholeSaleHelper,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->customerModel = $customerModel;
        $this->quoteConversationCollectionFactory = $conversationCollectionFactory;
        $this->pricingHelper = $pricingHelper;
        $this->quotesFactory = $quotesFactory;
        $this->productFactory = $productFactory;
        $this->imageHelper = $context->getImageHelper();
        $this->quoteRepository = $quoteRepository;
        $this->wholeSaleHelper= $wholeSaleHelper;
        parent::__construct($context, $data);
    }
    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getQuoteConversationCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'quotesystem.pager'
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
     * customer Id by customer session.
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerSession->getCustomerId();
    }
    /**
     * customer data by customer id.
     *
     * @return object
     */
    public function getCustomerData($customerId)
    {
        return $this->customerModel->load($customerId);
    }
    /**
     * get Collection of quotes conversation for particular quote id.
     *
     * @return collection
     */
    public function getQuoteConversationCollection()
    {
        if (!$this->quoteConversationCollection) {
            $quoteId = $this->getRequest()->getParam('id');
            if ($quoteId != 0) {
                $collection = $this->quoteConversationCollectionFactory
                    ->create()
                    ->addFieldToFilter('quote_id', $quoteId);
                $this->quoteConversationCollection = $collection;
            }
        }

        return $this->quoteConversationCollection;
    }
    /**
     * get formatted price by currency.
     *
     * @return format price string
     */
    public function getFormattedPrice($price)
    {
        return $this->pricingHelper
            ->currency($price, true, false);
    }
    /**
     * get quote load by id
     * @param  int $entityId
     */
    public function getQuoteData($entityId)
    {
        $quoteModel = $this->quoteRepository->getById($entityId);
        return $quoteModel;
    }
    /**
     * get product data by id
     * @param  int $productId
     */
    public function getProductData($productId)
    {
        $productModel = $this->productFactory->create()->load($productId);
        return $productModel;
    }
    /**
     * get imageHelper Object to get image of product
     */
    
    public function imageHelperObj()
    {
        return $this->imageHelper;
    }

    /**
     * Get Request parameters
     */
    public function getParameters()
    {
        return $this->getRequest()->getParams();
    }

    /**
     * Get WholeSale Helper Object
     */
    public function getWholeSaleHelper()
    {
        return $this->wholeSaleHelper;
    }
}
