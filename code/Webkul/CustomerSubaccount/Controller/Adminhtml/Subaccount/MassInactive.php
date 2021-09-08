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

namespace Webkul\CustomerSubaccount\Controller\Adminhtml\Subaccount;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\CustomerSubaccount\Model\ResourceModel\Subaccount\CollectionFactory;

class MassInactive extends \Magento\Backend\App\Action
{
    /**
     * Context
     *
     * @var \Magento\Backend\App\Action\Context
     */
    public $context;

    /**
     * Filter
     *
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * Subaccount Collection
     *
     * @var \Webkul\CustomerSubaccount\Model\ResourceModel\Subaccount\CollectionFactory
     */
    public $collectionFactory;

    /**
     * Logger
     *
     * @var \Webkul\CustomerSubaccount\Logger\Logger
     */
    public $logger;

    /**
     * Construct
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Webkul\CustomerSubaccount\Logger\Logger $logger
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Webkul\CustomerSubaccount\Logger\Logger $logger
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
       
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $count = 0;
            foreach ($collection as $subaccount) {
                $subaccount->setStatus(0);
                $subaccount->setAdminApproved(0);
                $subaccount->save();
                $count++;
            }
            $this->messageManager->addSuccess(__('A total of %1 sub-account(s) have been deactivated.', $count));
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/subaccount/index');
    }

    /**
     * check permission
     *
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_CustomerSubaccount::manage');
    }
}
