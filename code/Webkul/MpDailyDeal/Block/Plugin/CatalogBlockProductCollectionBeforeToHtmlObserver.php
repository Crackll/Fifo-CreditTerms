<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpDailyDeal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpDailyDeal\Block\Plugin;

use \Magento\Framework\App\Helper\Context;

class CatalogBlockProductCollectionBeforeToHtmlObserver
{
    /**
     *
     * @var \Webkul\MpDailyDeal\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * Review model
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @param Context                         $context
     * @param \Webkul\MpDailyDeal\Helper\Data $data
     */
    public function __construct(
        Context $context,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Webkul\MpDailyDeal\Helper\Data $data,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_helper = $data;
        $this->_reviewFactory = $reviewFactory;
        $this->_request = $request;
    }

    /**
     * @param \Magento\Review\Observer\CatalogBlockProductCollectionBeforeToHtmlObserver $subject
     * @param callable $proceed
     * @return string
     */
    public function aroundExecute(
        \Magento\Review\Observer\CatalogBlockProductCollectionBeforeToHtmlObserver $subject,
        callable $proceed,
        \Magento\Framework\Event\Observer $observer
    ) {
        $productCollection = $observer->getEvent()->getCollection();
        if ($this->_request->getFullActionName() == "mpdailydeal_index_index"
        || $this->_request->getFullActionName() == "mpdailydeal_collection_deal") {
            $dealProductIds = $this->_helper->getDealProductIds();
            $productCollection->addAttributeToFilter('entity_id', ['in' => $dealProductIds]);
            $observer->setCollection($productCollection);
        }
        $proceed($observer);
        return $this;
    }
}
