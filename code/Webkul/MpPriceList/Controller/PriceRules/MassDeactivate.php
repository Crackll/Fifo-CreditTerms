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

class MassDeactivate extends \Magento\Framework\App\Action\Action
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

    public function execute()
    {
        try {
            $ruleIds = [];
            $collection = $this->_filter->getCollection($this->_collectionFactory->create());
            foreach ($collection as $rule) {
                $this->updateItem($rule, 2);
            }
            $this->messageManager->addSuccess(__('Rule(s) deactivated successfully'));
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
     * @param object $item
     * @param int $status
     */
    private function updateItem($item, $status)
    {
        $item->setStatus($status)->save();
    }
}
