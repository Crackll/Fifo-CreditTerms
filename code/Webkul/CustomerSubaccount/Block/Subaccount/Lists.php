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

namespace Webkul\CustomerSubaccount\Block\Subaccount;

class Lists extends \Magento\Framework\View\Element\Template
{
    /**
     * Context
     *
     * @var \Magento\Framework\View\Element\Template\Context
     */
    public $context;

    /**
     * Subaccount Model
     *
     * @var \Webkul\CustomerSubaccount\Model\SubaccountFactory
     */
    public $subaccountFactory;

    /**
     * Helper
     *
     * @var \Webkul\CustomerSubaccount\Helper\Data
     */
    public $helper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\CustomerSubaccount\Model\SubaccountFactory $subaccountFactory
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\CustomerSubaccount\Model\SubaccountFactory $subaccountFactory,
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->subaccountFactory = $subaccountFactory;
        parent::__construct($context, $data);
        $param = $this->getRequest()->getParams();
        $mainAccId = $this->helper->getCustomerId();
        $customerIds = $this->helper->getCustomerIds();
        if (!in_array($mainAccId, $customerIds)) {
            $mainAccId = 0;
        }
        if ($this->helper->isSubaccountUser($mainAccId)) {
            $mainAccId = $this->helper->getSubAccount($mainAccId)->getMainAccountId();
        }
        $collection = $this->subaccountFactory->create()
                                ->getCollection();
        $customerGridFlat = $collection->getTable('customer_grid_flat');
        $collection->addFieldToFilter('main_account_id', $mainAccId)
                    ->addFieldToFilter('parent_account_id', ['in' => $customerIds])
                    ->addFieldToFilter('customer_id', ['in' => $customerIds]);
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
        if (isset($param['qn']) && $param['qn']) {
            $collection->addFieldToFilter('name', ['like'=>'%'.$param['qn'].'%']);
        }
        if (isset($param['qe']) && $param['qe']) {
            $collection->addFieldToFilter('email', ['like'=>'%'.$param['qe'].'%']);
        }
        if (isset($param['qs']) && is_numeric($param['qs'])) {
            $collection->addFieldToFilter('status', ['like'=>'%'.$param['qs'].'%']);
        }
        $this->setCollection($collection);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'wkcs.subaccount.record.pager'
            )->setCollection(
                $this->getCollection()
            );
            $this->setChild('pager', $pager);
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
     * Get Helper
     *
     * @return object
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
