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

class MassDelete extends \Magento\Framework\App\Action\Action
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
     * MassDelete Execute
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        try {
            $collection = $this->_filter->getCollection($this->_collectionFactory->create());
            foreach ($collection as $priceList) {
                $this->removeItem($priceList);
            }
            $this->messageManager->addSuccess(__('Pricelist(s) deleted successfully'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('mppricelist/sellerpricelist/managepricelist');
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath(
                'mppricelist/sellerpricelist/managepricelist',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * Remove Item
     *
     * @param object $item
     */
    private function removeItem($item)
    {
        $item->delete();
    }
}
