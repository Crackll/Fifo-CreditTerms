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
namespace Webkul\MpPriceList\Controller\PriceRules;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpPriceList\Model\ResourceModel\Rule\CollectionFactory;
use Webkul\MpPriceList\Model\ItemsFactory;

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
    * @param ItemsFactory $itemsFactory
    */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ItemsFactory $itemsFactory
    ) {
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->itemsFactory = $itemsFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $ruleIds = [];
            $collection = $this->_filter->getCollection($this->_collectionFactory->create());
            foreach ($collection as $rule) {
                $ruleIds[] = $rule->getId();
                $this->removeChildItems($rule->getId());
                $this->removeItem($rule);
            }
            $this->messageManager->addSuccess(__('Rule(s) deleted successfully'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('mppricelist/pricerules/manageruleslist');
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath(
                'mppricelist/pricerules/manageruleslist',
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

    /**
     * remove child Items
     *
     * @param int $ruleId
     * @return void
     */
    private function removeChildItems($ruleId)
    {
        $childItems = $this->itemsFactory->create()->getCollection()
         ->addFieldToFilter('parent_id', ['eq'=>$ruleId]);
        if (!empty($childItems)) {
            foreach ($childItems as $item) {
                $this->removeItem($item);
            }
        }
    }
}
