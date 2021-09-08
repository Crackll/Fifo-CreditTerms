<?php
/**
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpWholesale\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Class WholeSale Massdelete
 */
class Massaction extends \Magento\Backend\App\Action
{
    /**
     * WholeSale Products table
     */
    const TABLE_NAME = 'mpwholesale_product_details';
    const STATUS_DISABLE  = 0;
    const STATUS_ENABLE   = 1;
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
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->emailHelper = $emailHelper;
        $this->collectionFactory = $collectionFactory;
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
        $status = $this->getRequest()->getParam('id');
        $countRecord = $collection->getSize();
        try {
            if ($status == 1) {
                foreach ($collection as $item) {
                    $item->delete();
                }
                $this->messageManager->addSuccess(
                    __(
                        'A total of %1 record(s) have been deleted.',
                        $countRecord
                    )
                );
            } elseif ($status == 2) {
                $ids = [];
                foreach ($collection as $item) {
                    $ids[] = $item->getEntityId();
                }
                $where = ['entity_id IN (?)' => $ids];
                if (!empty($ids)) {
                    try {
                        $this->connection->beginTransaction();
                        $this->connection->update(
                            $this->resource->getTableName(self::TABLE_NAME),
                            ['status' => self::STATUS_DISABLE],
                            $where
                        );
                        $this->connection->commit();
                    } catch (\Exception $e) {
                        $this->connection->rollBack();
                    }
                }
                $this->messageManager->addSuccess(
                    __(
                        'A total of %1 record(s) have been disabled.',
                        $countRecord
                    )
                );
            } elseif ($status == 0) {
                $ids = [];
                foreach ($collection as $item) {
                    $ids[] = $item->getEntityId();
                }
                $where = ['entity_id IN (?)' => $ids];
                if (!empty($ids)) {
                    try {
                        $this->connection->beginTransaction();
                        $this->connection->update(
                            $this->resource->getTableName(self::TABLE_NAME),
                            ['status' => self::STATUS_ENABLE],
                            $where
                        );
                        $this->connection->commit();
                    } catch (\Exception $e) {
                        $this->connection->rollBack();
                    }
                }
                $this->messageManager->addSuccess(
                    __(
                        'A total of %1 record(s) have been enabled.',
                        $countRecord
                    )
                );
            } elseif ($status == 3) {
                $ids = [];
                foreach ($collection as $item) {
                    $ids[] = $item->getEntityId();
                }
                $where = ['entity_id IN (?)' => $ids];
                if (!empty($ids)) {
                    try {
                        $this->connection->beginTransaction();
                        $this->connection->update(
                            $this->resource->getTableName(self::TABLE_NAME),
                            ['approve_status' => self::STATUS_ENABLE],
                            $where
                        );
                        $this->connection->commit();
                        foreach ($ids as $id) {
                            $this->emailHelper->sendProductApprovalMail($id);
                        }
                    } catch (\Exception $e) {
                        $this->connection->rollBack();
                    }
                }
                $this->messageManager->addSuccess(
                    __(
                        'A total of %1 record(s) have been approved.',
                        $countRecord
                    )
                );
            } elseif ($status == 4) {
                $ids = [];
                foreach ($collection as $item) {
                    $ids[] = $item->getEntityId();
                }
                $where = ['entity_id IN (?)' => $ids];
                if (!empty($ids)) {
                    try {
                        $this->connection->beginTransaction();
                        $this->connection->update(
                            $this->resource->getTableName(self::TABLE_NAME),
                            ['approve_status' => self::STATUS_DISABLE],
                            $where
                        );
                        $this->connection->commit();
                        foreach ($ids as $id) {
                            $this->emailHelper->sendProductUnapprovalMail($id);
                        }
                    } catch (\Exception $e) {
                        $this->connection->rollBack();
                    }
                }
                $this->messageManager->addSuccess(
                    __(
                        'A total of %1 record(s) have been Rejected.',
                        $countRecord
                    )
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        // /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
    }
    
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpWholesale::productlist');
    }
}
