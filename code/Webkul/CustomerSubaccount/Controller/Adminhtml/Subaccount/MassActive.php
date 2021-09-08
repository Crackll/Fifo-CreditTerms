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

class MassActive extends \Magento\Backend\App\Action
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
     * Email
     *
     * @var \Webkul\CustomerSubaccount\Helper\Email
     */
    public $emailHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Webkul\CustomerSubaccount\Logger\Logger $logger
     * @param \Webkul\CustomerSubaccount\Helper\Email $emailHelper
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Webkul\CustomerSubaccount\Logger\Logger $logger,
        \Webkul\CustomerSubaccount\Helper\Email $emailHelper
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
        $this->emailHelper = $emailHelper;
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
                if (!$subaccount->getStatus()) {
                    $this->emailHelper->sendSubAccountActivatedNotificationFromAdmin($subaccount);
                    $this->emailHelper->sendSubAccountActivatedNotificationFromAdminToMainAccount($subaccount);
                }
                $subaccount->setStatus(1);
                $subaccount->setAdminApproved(1);
                $subaccount->save();
                $count++;
            }
            $this->messageManager->addSuccess(__('A total of %1 sub-account(s) have been activated.', $count));
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
