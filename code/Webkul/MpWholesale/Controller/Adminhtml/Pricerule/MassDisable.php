<?php
/**
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Controller\Adminhtml\Pricerule;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpWholesale\Model\ResourceModel\PriceRule\CollectionFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Class WholeSale MassDisable
 */
class MassDisable extends \Magento\Backend\App\Action
{
    /**
     * WholeSale Price Rule table
     */
    const TABLE_NAME = 'mpwholesale_price_rules';
    const STATUS  = 0;
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
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        ResourceConnection $resource,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
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
        $countRecord = $collection->getSize();
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
                    ['status' => self::STATUS],
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

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
    }
}
