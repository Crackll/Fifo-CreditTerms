<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPriceList
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPriceList\Controller\SellerPriceList;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpPriceList\Model\ResourceModel\PriceList\CollectionFactory;

class MassActivate extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        try {
            $collection = $this->_filter->getCollection($this->_collectionFactory->create());
            foreach ($collection as $priceList) {
                $this->updateItem($priceList, 1);
            }
            $this->messageManager->addSuccess(
                __('Pricelist(s) activated successfully')
            );
            /** @var \Magento\Framework\Controller\Result\RedirectFactory $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            return $this->resultRedirectFactory->create()->setPath(
                'mppricelist/sellerpricelist/managepricelist',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath(
                'mppricelist/sellerpricelist/managepricelist',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * @param object $item
     * @param int $status
     */
    private function updateItem($item, $status)
    {
        $item->setStatus($status)->save();
    }
}
