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

namespace Webkul\CustomerSubaccount\Block\MyCarts;

class Lists extends \Magento\Framework\View\Element\Template
{
    /**
     * Context
     *
     * @var \Magento\Framework\View\Element\Template\Context
     */
    public $context;

    /**
     * Subaccount
     *
     * @var \Webkul\CustomerSubaccount\Model\SubaccountFactory
     */
    public $subaccountFactory;

    /**
     * Subaccount Cart
     *
     * @var \Webkul\CustomerSubaccount\Model\CartFactory
     */
    public $subaccCartFactory;

    /**
     * Helper
     *
     * @var \Webkul\CustomerSubaccount\Helper\Data
     */
    public $helper;

    /**
     * Quote
     *
     * @var \Magento\Quote\Model\QuoteFactory
     */
    public $quoteFactory;

    /**
     * Pricing Helper
     *
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    public $pricingHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\CustomerSubaccount\Model\SubaccountFactory $subaccountFactory
     * @param \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\CustomerSubaccount\Model\SubaccountFactory $subaccountFactory,
        \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory,
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->subaccountFactory = $subaccountFactory;
        $this->subaccCartFactory = $subaccCartFactory;
        $this->quoteFactory = $quoteFactory;
        $this->pricingHelper = $pricingHelper;
        parent::__construct($context, $data);
        $param = $this->getRequest()->getParams();
        $customerId = $this->helper->getCustomerId();
        $collection = $this->subaccCartFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter('customer_id', ['eq'=>$customerId]);
        if (isset($param['qid']) && $param['qid']) {
            $collection->addFieldToFilter('entity_id', ['eq'=>$param['qid']]);
        }
        if (isset($param['qt']) && $param['qt']) {
            $collection->addFieldToFilter('type', ['eq'=>$param['qt']]);
        }
        if (isset($param['qs']) && $param['qs']) {
            $status = $param['qs'];
            if ($status == 'p') {
                $collection->addFieldToFilter('status', ['eq'=>0]);
            } elseif ($status == 'o') {
                $collection->addFieldToFilter('status', ['eq'=>2]);
            } else {
                $collection->addFieldToFilter('status', ['eq'=>1]);
                if ($status == 'a') {
                    $collection->addFieldToFilter('type', ['eq'=>1]);
                } elseif ($status == 'm') {
                    $collection->addFieldToFilter('type', ['eq'=>2]);
                }
            }
        }
        $collection->setOrder('entity_id', 'desc');
        $this->setCollection($collection);
    }

    /**
     * Prepare Layout
     *
     * @return \Webkul\CustomerSubaccount\Block\MyCarts\Lists
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'wkcs.mycarts.record.pager'
            )->setCollection(
                $this->getCollection()
            );
            $this->setChild('pager', $pager);
        }
        return $this;
    }
 
    /**
     * Get Pager Html
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get Helper
     *
     * @return \Webkul\CustomerSubaccount\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Get Quote
     *
     * @param int $quoteId
     * @return \Magento\Quote\Model\QuoteFactory
     */
    public function getQuote($quoteId)
    {
        return $this->quoteFactory->create()->load($quoteId);
    }

    /**
     * Get Formatted Price
     *
     * @param int $price
     * @return string
     */
    public function getFormattedPrice($price)
    {
        return $this->pricingHelper->currency($price, true, false);
    }

    /**
     * Get Statuses
     *
     * @return array
     */
    public function getStatuses()
    {
        $arr = [];
        $arr[''] = '';
        $arr['p'] = __('Pending');
        $arr['a'] = __('Approved');
        $arr['m'] = __('Merged');
        $arr['o'] = __('Ordered');
        return $arr;
    }
}
