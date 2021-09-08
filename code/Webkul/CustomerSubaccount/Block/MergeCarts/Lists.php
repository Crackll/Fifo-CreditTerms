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

namespace Webkul\CustomerSubaccount\Block\MergeCarts;

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
     * Customer
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    public $customerFactory;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\CustomerSubaccount\Model\SubaccountFactory $subaccountFactory
     * @param \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\CustomerSubaccount\Model\SubaccountFactory $subaccountFactory,
        \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory,
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->subaccountFactory = $subaccountFactory;
        $this->subaccCartFactory = $subaccCartFactory;
        $this->quoteFactory = $quoteFactory;
        $this->pricingHelper = $pricingHelper;
        $this->customerFactory = $customerFactory;
        parent::__construct($context, $data);
        $param = $this->getRequest()->getParams();
        $customerId = $this->helper->getCustomerId();
        $mainAccId = $this->helper->getMainAccountId($customerId);
        $customerIds = $this->helper->getCustomerIds();
        $subAccountIds = $this->subaccountFactory->create()
                                ->getCollection()
                                ->addFieldToFilter('main_account_id', $mainAccId)
                                ->getColumnValues('customer_id');
        if (empty($subAccountIds)) {
            $subAccountIds = [0];
        } else {
            $subAccountIds = array_intersect($subAccountIds, $customerIds);
        }
        $collection = $this->subaccCartFactory->create()
                                    ->getCollection();
        $customerGridFlat = $collection->getTable('customer_grid_flat');
        $collection->addFieldToFilter('type', 2)
                    ->addFieldToFilter('status', 0)
                    ->addFieldToFilter('customer_id', ['in'=>$subAccountIds]);
        $collection->getSelect()->join(
            $customerGridFlat.' as cgf',
            'main_table.customer_id = cgf.entity_id',
            [
                            'name' => 'name',
                            'email' => 'email'
                        ]
        );
        $collection->addFilterToMap('name', 'name');
        $collection->addFilterToMap('email', 'email');
        if (isset($param['qid']) && $param['qid']) {
            $collection->addFieldToFilter('entity_id', ['eq'=>$param['qid']]);
        }
        if (isset($param['qn']) && $param['qn']) {
            $collection->addFieldToFilter('name', ['like'=>'%'.$param['qn'].'%']);
        }
        if (isset($param['qe']) && $param['qe']) {
            $collection->addFieldToFilter('email', ['like'=>'%'.$param['qe'].'%']);
        }
        $collection->setOrder('entity_id', 'desc');
        $this->setCollection($collection);
    }

    /**
     * Prepare Layout
     *
     * @return \Webkul\CustomerSubaccount\Block\MergeCarts\Lists
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'wkcs.mergecarts.record.pager'
            )->setCollection(
                $this->getCollection()
            );
            $this->setChild('pager', $pager);
        }
        return $this;
    }
 
    /**
     * Pager Html
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
     * Get Formatter Price
     *
     * @param int $price
     * @return string
     */
    public function getFormattedPrice($price)
    {
        return $this->pricingHelper->currency($price, true, false);
    }
}
