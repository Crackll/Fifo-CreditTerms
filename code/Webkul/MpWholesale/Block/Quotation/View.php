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
use Webkul\Marketplace\Helper\Data;
use Webkul\MpWholesale\Helper\Data as MpWholesaleHelper;
use Webkul\MpWholesale\Model\QuotesFactory;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;
    
    /**
     * @var \Webkul\MpWholesale\Helper\Data
     */
    protected $mpWholesaleHelper;
    
    /**
     * @var \Webkul\MpWholesale\Model\QuotesFactory
     */
    protected $quotesFactory;
    
    /**
    * @var quoteCollection
    */
    protected $_quoteCollection;
    
    /**
     * @param Context $context
     * @param Data $mpHelper
     * @param MpWholesaleHelper $mpWholesaleHelper
     * @param QuotesFactory $quotesFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $mpHelper,
        MpWholesaleHelper $mpWholesaleHelper,
        QuotesFactory $quotesFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->mpHelper = $mpHelper;
        $this->mpWholesaleHelper = $mpWholesaleHelper;
        $this->quotesFactory = $quotesFactory;
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getQuotesCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'quotation.pager'
            )
            ->setCollection(
                $this->getQuotesCollection()
            );
            $this->setChild('pager', $pager);
            $this->getQuotesCollection()->load();
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
     * get Collection of quotes
     *
     * @return collection
     */
    public function getQuotesCollection()
    {
        if (!$this->_quoteCollection) {
            $sellerId = $this->mpHelper->getCustomerId();
            $collection = $this->quotesFactory->create()
                        ->getCollection()
                        ->addFieldToFilter(
                            'seller_id',
                            $sellerId
                        )
                        ->setOrder('entity_id', 'DESC');
            $this->_quoteCollection = $collection;
        }
        return $this->_quoteCollection;
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
