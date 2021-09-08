<?php
/**
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPurchaseManagement\Controller\Adminhtml\Order;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\ResourceConnection;
use \Webkul\MpPurchaseManagement\Model\Order;

class Merge extends \Webkul\MpPurchaseManagement\Controller\Adminhtml\Order
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Webkul\MpPurchaseManagement\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Webkul\MpPurchaseManagement\Model\OrderItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var \Webkul\MpPurchaseManagement\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\MpWholesale\Helper\Data
     */
    protected $wholesaleHelper;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @param \Magento\Backend\App\Action\Context                  $context
     * @param \Magento\Ui\Component\MassAction\Filter              $filter
     * @param \Webkul\MpPurchaseManagement\Model\OrderFactory      $orderFactory
     * @param \Webkul\MpPurchaseManagement\Model\OrderItemFactory  $orderItemFactory
     * @param \Webkul\MpPurchaseManagement\Helper\Data             $helper
     * @param \Webkul\MpWholesale\Helper\Data                      $wholesaleHelper
     * @param ResourceConnection                                   $resource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Webkul\MpPurchaseManagement\Model\OrderFactory $orderFactory,
        \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory,
        \Webkul\MpPurchaseManagement\Helper\Data $helper,
        \Webkul\MpWholesale\Helper\Data $wholesaleHelper,
        ResourceConnection $resource
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->orderFactory = $orderFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->helper = $helper;
        $this->wholesaleHelper = $wholesaleHelper;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
    }

    /**
     * merge purchase order
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->orderFactory->create()->getCollection());
        $flag = false;
        $lastOrderWholesaler = $collection->getFirstItem()->getWholesalerId();
        $orderIds = [];
        $grandTotal = 0;
        $orderCurrencyCode = '';
        foreach ($collection as $order) {
            if ($order->getWholesalerId()!=$lastOrderWholesaler || $order->getStatus()!=Order::STATUS_NEW) {
                $flag = true;
                break;
            }
            array_push($orderIds, $order->getEntityId());
            $grandTotal = $grandTotal + $order->getGrandTotal();
            $orderCurrencyCode = $order->getOrderCurrencyCode();
        }
        if ($flag || $collection->getSize()<2) {
            $this->messageManager->addError(__('Order(s) could not be Merged'));
        } else {
            //create new merged order
            $newOrder = $this->orderFactory->create();
            $newOrder->setStatus(Order::STATUS_NEW);
            $newOrder->setWholesalerId($lastOrderWholesaler);
            $newOrder->setSource('Merged #'.implode(',', $orderIds));
            $newOrder->setIncrementId('M'.$this->helper->getNewIncrementId($orderIds[0]));
            $newOrder->setGrandTotal($grandTotal);
            $newOrder->setOrderCurrencyCode($orderCurrencyCode);
            $newOrder->save();

            $where = ['purchase_order_id IN (?)' => $orderIds];
            $orderWhere = ['entity_id IN (?)' => $orderIds];
            try {
                //update order items
                $this->connection->beginTransaction();
                $this->connection->update(
                    $this->resource->getTableName(\Webkul\MpPurchaseManagement\Model\OrderItem::TABLE_NAME),
                    ['purchase_order_id' => $newOrder->getEntityId()],
                    $where
                );
                //cancel old orders
                $this->connection->update(
                    $this->resource->getTableName(\Webkul\MpPurchaseManagement\Model\Order::TABLE_NAME),
                    ['status' => \Webkul\MpPurchaseManagement\Model\Order::STATUS_CANCELLED],
                    $orderWhere
                );
                $this->connection->commit();
                $this->messageManager->addSuccess(__('%1 Purchase Orders successfully merged', $collection->getSize()));
            } catch (\Exception $e) {
                $this->connection->rollBack();
                $this->messageManager->addError($e->getMessage());
            }

            //send mail
            $customerIds = [];
            $orderItemCollection = $this->orderItemFactory->create()->getCollection()
                                        ->addFieldToFilter('purchase_order_id', ['eq'=>$newOrder->getEntityId()]);
            foreach ($orderItemCollection as $item) {
                if (!in_array($item->getSellerId(), $customerIds)) {
                    array_push($customerIds, $item->getSellerId());
                }
            }
            $this->sendMergeMail($customerIds, $newOrder);
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
    }

    /**
     * send new merged order mail to seller
     * @param array
     * @param  \Webkul\MpPurchaseManagement\Model\Order
     * @return void
     */
    protected function sendMergeMail($customerIds, $order)
    {
        foreach ($customerIds as $id) {
            $customer = $this->wholesaleHelper->getCustomerData($id);
            $data = [
                'var_name' => $customer->getName(),
                'var_message' => __(
                    'New merged Purchase Order %1 has been created by Admin and
                                     your old purchase order has been cancelled now.',
                    $order->getIncrementId()
                ),
                'sender_email' => $this->wholesaleHelper->getAdminEmail(),
                'sender_name' => $this->wholesaleHelper->getAdminName(),
                'receiver_name' => $customer->getName(),
                'receiver_mail' => $customer->getEmail(),
                'subject' => __('Purchase Order Merged')
            ];
            $this->helper->sendUpdateStatusMail($data);
        }
    }
}
