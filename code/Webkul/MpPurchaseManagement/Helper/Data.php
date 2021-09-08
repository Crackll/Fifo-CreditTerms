<?php
/**
 * MpPurchaseManagement Helper
 *
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPurchaseManagement\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_NEW_PURCHASE_ORDER = 'mpwholsale/mp_purchase_management/new_order';
    const XML_PATH_UPDATE_STATUS = 'mpwholsale/mp_purchase_management/update_status';

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $product;

    /**
     * @var Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Webkul\MpWholesale\Helper\Email
     */
    protected $wholesaleEmailHelper;

    /**
     * @var \Webkul\MpWholesale\Helper\Data
     */
    protected $wholesaleHelper;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var string
     */
    protected $tempId;

    /**
     * @param \Magento\Framework\App\Helper\Context                 $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterface       $product
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface  $stockRegistry
     * @param \Webkul\MpWholesale\Helper\Email                      $wholesaleEmailHelper
     * @param \Webkul\MpWholesale\Helper\Data                       $wholesaleHelper
     * @param \Magento\Framework\Translate\Inline\StateInterface    $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder     $transportBuilder
     * @param \Magento\Framework\Message\ManagerInterface           $messageManager
     * @param \Magento\Framework\Json\Helper\Data                   $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $product,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Webkul\MpWholesale\Helper\Email $wholesaleEmailHelper,
        \Webkul\MpWholesale\Helper\Data $wholesaleHelper,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        parent::__construct($context);
        $this->product = $product;
        $this->stockRegistry = $stockRegistry;
        $this->wholesaleEmailHelper = $wholesaleEmailHelper;
        $this->wholesaleHelper = $wholesaleHelper;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->messageManager = $messageManager;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * get module status
     * @return bool
     */
    public function getModuleStatus()
    {
        return $this->scopeConfig->getValue(
            'mpwholsale/mp_purchase_management/enable_disable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Allow seller to receive shipment
     * @return bool
     */
    public function isSellerShipmentAllowed()
    {
        return $this->scopeConfig->getValue(
            'mpwholsale/mp_purchase_management/allow_shipment_seller',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Order Approval Required
     * @return bool
     */
    public function isOrderApprovalRequired()
    {
        return $this->scopeConfig->getValue(
            'mpwholsale/mp_purchase_management/order_approval_required',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get customer account navigation template
     * @return string
     */
    public function getNavigationTemplate()
    {
        if ($this->getModuleStatus()) {
            return 'Webkul_MpPurchaseManagement::account/navigation.phtml';
        } else {
            return 'Webkul_MpWholesale::account/navigation.phtml';
        }
    }

    /**
     * get seller account navigation template
     * @return string
     */
    public function getSellerNavigationTemplate()
    {
        if ($this->getModuleStatus()) {
            return 'Webkul_MpPurchaseManagement::layout2/account/navigation.phtml';
        } else {
            return 'Webkul_MpWholesale::layout2/account/navigation.phtml';
        }
    }

    /**
     * generate template for email
     * @param  $emailTemplateVariables
     * @param  $senderInfo
     * @param  $receiverInfo
     * @return void
     */
    public function generateTemplate(
        $emailTemplateVariables,
        $senderInfo,
        $receiverInfo
    ) {
        $template =  $this->transportBuilder->setTemplateIdentifier($this->tempId)->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => 0]
        )->setTemplateVars($emailTemplateVariables)->setFrom($senderInfo)
                            ->addTo($receiverInfo['email'], $receiverInfo['name']);
    }

    /**
     * Send New Purchase Order Mail
     * @param  \Webkul\MpPurchaseManagement\Model\Order
     * @param \Webkul\MpPurchaseManagement\Model\OrderItem
     * @return void
     */
    public function sendNewPurchaseOrderMail($order, $orderItem)
    {
        $customer = $this->wholesaleHelper->getCustomerData($orderItem->getSellerId());
        $emailTemplateVariables = [
            'name' => $customer->getName(),
            'increment_id' => $order->getIncrementId(),
            'product_name' => $this->wholesaleHelper->getProduct($orderItem->getProductId())->getName(),
            'wholesaler' => $this->wholesaleHelper->getWholesalerData($order->getWholesalerId())->getName(),
            'qty' => $orderItem->getQuantity(),
            'price' => $orderItem->getPrice(),
            'total' => $orderItem->getPrice()*$orderItem->getQuantity()
        ];
        $senderInfo = [
            'name' => $this->wholesaleHelper->getAdminName(),
            'email' => $this->wholesaleHelper->getAdminEmail()
        ];
        $receiverInfo = [
            'name' => $customer->getName(),
            'email' => $customer->getEmail()
        ];
        $this->tempId = $this->wholesaleEmailHelper->getTemplateId(self::XML_PATH_NEW_PURCHASE_ORDER);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->inlineTranslation->resume();
    }

    /**
     * Send Order Update Status Mail
     * @param  array
     * @return void
     */
    public function sendUpdateStatusMail($data)
    {
        $emailTemplateVariables = [
            'name' => $data['var_name'],
            'message' => $data['var_message'],
            'subject' => $data['subject']
        ];
        $senderInfo = [
            'name' => $data['sender_name'],
            'email' => $data['sender_email']
        ];
        $receiverInfo = [
            'name' => $data['receiver_name'],
            'email' => $data['receiver_mail']
        ];
        $this->tempId = $this->wholesaleEmailHelper->getTemplateId(self::XML_PATH_UPDATE_STATUS);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->inlineTranslation->resume();
    }

    /**
     * Create new increment ID for purchase order
     * @param  int
     * @return string
     */
    public function getNewIncrementId($quote_id)
    {
        $prefix = $this->scopeConfig->getValue(
            'mpwholsale/mp_purchase_management/prefix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $padding = $this->scopeConfig->getValue(
            'mpwholsale/mp_purchase_management/padding',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($padding) {
            $increment_id = str_pad($quote_id, $padding, '0', STR_PAD_LEFT);
            if ($prefix) {
                $increment_id = $prefix.$increment_id;
            } else {
                $increment_id = '1'.$increment_id;
            }
        } else {
            $increment_id = $quote_id;
        }
        return $increment_id;
    }

    /**
     * For Update stock of product
     * @param int $productId
     * @param int $receiveQty
     * @return void
     */
    public function updateProductStock($productId, $receiveQty)
    {
        $product = $this->product->getById($productId); //load product which you want to update stock
        $sku = $product->getSku();
        $stockItem = $this->stockRegistry->getStockItem($productId); // load stock of that product
        $totalQuantity = $receiveQty + $stockItem->getQty();
        $stockItem->setQty($totalQuantity);
        $this->stockRegistry->updateStockItemBySku($sku, $stockItem);
    }

    /**
     * This function will return json encoded data
     *
     * @param json $data
     * @return Array
     */
    public function jsonEncodeData($data)
    {
        return $this->jsonHelper->jsonEncode($data, true);
    }
}
