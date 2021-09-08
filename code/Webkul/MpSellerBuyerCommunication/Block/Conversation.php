<?php
/**
 * Webkul Software
 *
 * @category    Webkul
 * @package     Webkul_MpSellerBuyerCommunication
 * @author      Webkul
 * @copyright   Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerBuyerCommunication\Block;

use Magento\Framework\View\Element\Template;
use Webkul\MpSellerBuyerCommunication\Model\ResourceModel\SellerBuyerCommunication\CollectionFactory;
use Webkul\MpSellerBuyerCommunication\Model\ResourceModel\Conversation\CollectionFactory as ConversationFactory;

/**
 * Conversation block
 *
 * @author      Webkul Software
 */
class Conversation extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Webkul\MpSellerBuyerCommunication\Model\ResourceModel\SellerBuyerCommunication\CollectionFactory
     */
    protected $sellerBuyerCommunicationCollectionFactory;

    /**
     * @var Webkul\MpSellerBuyerCommunication\Model\ResourceModel\Conversation\CollectionFactory
     */
    protected $conversationCollectionFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @param Context $context
     * @param array $data
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CollectionFactory $sellerBuyerCommunicationCollectionFactory,
        ConversationFactory $conversationCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->sellerBuyerCommunicationCollectionFactory = $sellerBuyerCommunicationCollectionFactory;
        $this->conversationCollectionFactory = $conversationCollectionFactory;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getConversationCollection()
    {
        if (!($customerId = $this->customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->conversationList) {
            $id = $this->getRequest()->getParam("id");

            $collection = $this->conversationCollectionFactory->create()->addFieldToSelect(
                '*'
            )
            ->addFieldToFilter(
                'comm_id',
                $id
            );
            $this->conversationList = $collection;
        }
        return $this->conversationList;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getConversationCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'mpsellerbuyercommunication.conversation.list.pager'
            )
            ->setAvailableLimit([4=>4,8=>8,16=>16])
            ->setCollection(
                $this->getConversationCollection()
            );
            $this->setChild('pager', $pager);
            $this->getConversationCollection()->load();
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
}
