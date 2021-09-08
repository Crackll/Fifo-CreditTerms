<?php
 /**
  * @category  Webkul
  * @package   Webkul_MpPurchaseManagement
  * @author    Webkul
  * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
  * @license   https://store.webkul.com/license.html
  */
namespace Webkul\MpPurchaseManagement\Block\Adminhtml\Order;

use \Webkul\MpPurchaseManagement\Model\Order;

class View extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Webkul\MpPurchaseManagement\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Webkul\MpWholesale\Helper\Data
     */
    protected $wholesaleHelper;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Webkul\MpWholesale\Helper\Data $wholesaleHelper,
        \Webkul\MpPurchaseManagement\Model\OrderFactory $orderFactory,
        array $data = []
    ) {
        $this->wholesaleHelper = $wholesaleHelper;
        $this->orderFactory = $orderFactory;
        parent::__construct($context, $data);
    }

    /**
     * Initialize edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Webkul_MpPurchaseManagement';
        $this->_controller = 'adminhtml_order';
        parent::_construct();
        $orderId = $this->getRequest()->getParam('id');
        $order = $this->orderFactory->create()->load($orderId);
        if ($order->getStatus()==Order::STATUS_NEW) {
            $this->buttonList->update('delete', 'label', __('Cancel'));
            $this->buttonList->update('save', 'label', __('Confirm'));
        } else {
            $this->buttonList->remove('delete');
            $this->buttonList->remove('save');
        }
        if ($order->getStatus()==Order::STATUS_SHIPPED) {
            $this->buttonList->add(
                'wk-print-ship',
                [
                    'label' => __('Print Shipment'),
                    'onclick' => 'setLocation(\'' . $this->getPrintShipmentUrl() . '\')',
                    'class' => 'print-ship'
                ]
            );
        }
        $user = $this->wholesaleHelper->getCurrentUser();

        if ($user->getRole()->getRoleName() == 'Wholesaler' && $order->getStatus()==Order::STATUS_PROCESSING) {
            $this->buttonList->add(
                'wk-po-ship',
                [
                    'label' => __('Ship'),
                    'class' => 'ship'
                ]
            );
        }
        if ($user->getRole()->getRoleName() == 'Administrators' && $order->getStatus()==Order::STATUS_SHIPPED) {
            $this->buttonList->add(
                'receive',
                [
                    'label' => __('Receive Shipment'),
                    'class' => 'receive',
                    'onclick' => 'deleteConfirm(\'' . __(
                        'Are you sure you received the items in this order?'
                    ) . '\', \'' . $this->getReceiveShipmentUrl() . '\')'
                ]
            );
        }
        $this->buttonList->remove('reset');
    }

    /**
     * Get cancel order URL
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl(
            '*/*/update',
            [$this->_objectId => $this->getRequest()->getParam($this->_objectId),
            'status' => Order::STATUS_CANCELLED]
        );
    }

    /**
     * Get confirm order URL
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl(
            '*/*/update',
            [$this->_objectId => $this->getRequest()->getParam($this->_objectId),
            'status' => Order::STATUS_PROCESSING]
        );
    }

    /**
     * Get receive shipment of order items URL
     * @return string
     */
    public function getReceiveShipmentUrl()
    {
        return $this->getUrl('*/*/receive', [$this->_objectId => $this->getRequest()->getParam($this->_objectId)]);
    }

    /**
     * Get print shipment of order items URL
     * @return string
     */
    public function getPrintShipmentUrl()
    {
        return $this->getUrl('*/*/print', [$this->_objectId => $this->getRequest()->getParam($this->_objectId)]);
    }
}
