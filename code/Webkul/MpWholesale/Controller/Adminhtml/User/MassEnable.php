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
 * Class WholeSale MassEnable
 */
class MassEnable extends \Magento\Backend\App\Action
{
    /**
     * WholeSale User table
     */
    const TABLE_NAME = 'mpwholesale_userdata';
    const STATUS  = 1;
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
            $ids[] = $item->getEntityId();
            $userIds[] = $item->getUserId();
        }
        $where = ['entity_id IN (?)' => $ids];
        
        if (!empty($ids)) {
            try {
                $this->connection->beginTransaction();
                $this->connection->update(
                    $this->resource->getTableName(self::TABLE_NAME),
                    ['status' => self::STATUS],
                    $where
                );
                $this->connection->commit();
                foreach ($userIds as $userId) {
                    $this->setUserStatus($userId);
                }
                foreach ($ids as $id) {
                    $this->emailHelper->sendWholesalerApprovalMail($id);
                }
            } catch (\Exception $e) {
                $this->connection->rollBack();
            }
        }
        $this->messageManager->addSuccess(
            __(
                'A total of %1 record(s) have been Active.',
                $countRecord
            )
        );

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
    }
    
    /**
     * User Active function
     *
     * @param integer $id
     * @return void
     */
    public function setUserStatus($id)
    {
        $model = $this->userFactory->create()->load($id);
        $model->setIsActive(self::STATUS);
        $model->save();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpWholesale::user');
    }
}
