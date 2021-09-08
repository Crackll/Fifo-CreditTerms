<?php
/**
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Controller\Adminhtml\User;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpWholesale\Model\ResourceModel\WholeSaleUser\CollectionFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Class WholeSale MassDelete
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * WholeSale User table
     */
    const TABLE_NAME = 'mpwholesale_userdata';
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param ResourceConnection $resource
     * @param \Webkul\MpWholesale\Helper\Email $emailHelper
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        ResourceConnection $resource,
        \Webkul\MpWholesale\Helper\Email $emailHelper,
        CollectionFactory $collectionFactory,
        \Magento\User\Model\UserFactory $userFactory
    ) {
        $this->filter = $filter;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->emailHelper = $emailHelper;
        $this->collectionFactory = $collectionFactory;
        $this->userFactory = $userFactory;
        parent::__construct($context);
    }
    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $countRecord = $collection->getSize();
        $ids = [];
        $userIds = [];
        foreach ($collection as $item) {
            $this->deleteUser($item->getUserId());
        }
        
        $collection->walk('delete');

        $this->messageManager->addSuccess(
            __(
                'A total of %1 record(s) have been Deleted.',
                $countRecord
            )
        );

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
    }
    
    /**
     * User Delete function
     *
     * @param integer $id
     * @return void
     */
    public function deleteUser($id)
    {
        $model = $this->userFactory->create();
        $model->setId($id);
        $model->delete();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpWholesale::user');
    }
}
