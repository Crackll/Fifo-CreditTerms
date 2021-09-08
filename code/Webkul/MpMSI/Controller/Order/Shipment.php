<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMSI
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMSI\Controller\Order;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Order\Shipment as OrderShipment;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Webkul\Marketplace\Helper\Notification as NotificationHelper;
use Webkul\Marketplace\Model\Notification;
use Webkul\Marketplace\Helper\Data as HelperData;
use Webkul\Marketplace\Model\SaleslistFactory;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;
use Webkul\Marketplace\Model\OrdersFactory as MpOrdersModel;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection as InvoiceCollection;
use Webkul\Marketplace\Model\SellerFactory as MpSellerModel;

/**
 * Webkul MpMSI Order Shipment Controller.
 */
class Shipment extends \Webkul\Marketplace\Controller\Order\Shipment
{
    /**
     * @var \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku
     */
    protected $sourceDataBySku;
    /**
     * @param \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku $sourceDataBySku
     */
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param InvoiceSender $invoiceSender
     * @param ShipmentSender $shipmentSender
     * @param ShipmentFactory $shipmentFactory
     * @param OrderShipment $shipment
     * @param CreditmemoSender $creditmemoSender
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param CreditmemoFactory $creditmemoFactory
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param StockConfigurationInterface $stockConfiguration
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderManagementInterface $orderManagement
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Marketplace\Helper\Orders $orderHelper
     * @param NotificationHelper $notificationHelper
     * @param HelperData $helper
     * @param \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement
     * @param SaleslistFactory $saleslistFactory
     * @param CustomerUrl $customerUrl
     * @param DateTime $date
     * @param FileFactory $fileFactory
     * @param \Webkul\Marketplace\Model\Order\Pdf\Creditmemo $creditmemoPdf
     * @param \Webkul\Marketplace\Model\Order\Pdf\Invoice $invoicePdf
     * @param MpOrdersModel $mpOrdersModel
     * @param InvoiceCollection $invoiceCollection
     * @param \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement
     * @param \Magento\Catalog\Model\ProductFactory $productModel
     * @param MpSellerModel $mpSellerModel
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku $sourceDataBySku
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        InvoiceSender $invoiceSender,
        ShipmentSender $shipmentSender,
        ShipmentFactory $shipmentFactory,
        OrderShipment $shipment,
        CreditmemoSender $creditmemoSender,
        CreditmemoRepositoryInterface $creditmemoRepository,
        CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        StockConfigurationInterface $stockConfiguration,
        OrderRepositoryInterface $orderRepository,
        OrderManagementInterface $orderManagement,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Marketplace\Helper\Orders $orderHelper,
        NotificationHelper $notificationHelper,
        HelperData $helper,
        \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement,
        SaleslistFactory $saleslistFactory,
        CustomerUrl $customerUrl,
        DateTime $date,
        FileFactory $fileFactory,
        \Webkul\Marketplace\Model\Order\Pdf\Creditmemo $creditmemoPdf,
        \Webkul\Marketplace\Model\Order\Pdf\Invoice $invoicePdf,
        MpOrdersModel $mpOrdersModel,
        InvoiceCollection $invoiceCollection,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement,
        \Magento\Catalog\Model\ProductFactory $productModel,
        MpSellerModel $mpSellerModel,
        \Psr\Log\LoggerInterface $logger,
        \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku $sourceDataBySku
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $invoiceSender,
            $shipmentSender,
            $shipmentFactory,
            $shipment,
            $creditmemoSender,
            $creditmemoRepository,
            $creditmemoFactory,
            $invoiceRepository,
            $stockConfiguration,
            $orderRepository,
            $orderManagement,
            $coreRegistry,
            $customerSession,
            $orderHelper,
            $notificationHelper,
            $helper,
            $creditmemoManagement,
            $saleslistFactory,
            $customerUrl,
            $date,
            $fileFactory,
            $creditmemoPdf,
            $invoicePdf,
            $mpOrdersModel,
            $invoiceCollection,
            $invoiceManagement,
            $productModel,
            $mpSellerModel,
            $logger
        );
        $this->sourceDataBySku = $sourceDataBySku;
    }
    /**
     * Get Shipping Qty from Multiple Source
     *
     * @param [type] $order
     * @param [type] $items
     * @return void
     */
    protected function _getShippingItemQtys($order, $items)
    {
        $paramData = $this->getRequest()->getParams();
        $data = [];
        $subtotal = 0;
        $baseSubtotal = 0;
        
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getItemId(), $items)) {
                if (isset($paramData['sourceCode'])) {
                    $sourceQty = $this->getSourceQty($paramData['sourceCode'], $item->getSku());
                    if ((int)($item->getQtyOrdered() - $item->getQtyShipped()) > $sourceQty) {
                        $data[$item->getItemId()] = $sourceQty;
                    } else {
                        $data[$item->getItemId()] = (int)($item->getQtyOrdered() - $item->getQtyShipped());
                    }
                } else {
                    $data[$item->getItemId()] = (int)($item->getQtyOrdered() - $item->getQtyShipped());
                }
                
                $_item = $item;

                // for bundle product
                // $bundleitems = array_merge([$_item], $_item->getChildrenItems());
                $bundleitems = $this->mergeArray($_item);

                if ($_item->getParentItem()) {
                    continue;
                }

                if ($_item->getProductType() == 'bundle') {
                    foreach ($bundleitems as $_bundleitem) {
                        if ($_bundleitem->getParentItem()) {
                            $data[$_bundleitem->getItemId()] = (int)(
                                $_bundleitem->getQtyOrdered() - $item->getQtyShipped()
                            );
                        }
                    }
                }
                $subtotal += $_item->getRowTotal();
                $baseSubtotal += $_item->getBaseRowTotal();
            } else {
                if (!$item->getParentItemId()) {
                    $data[$item->getItemId()] = 0;
                }
            }
        }

        return ['data' => $data,'subtotal' => $subtotal,'baseSubtotal' => $baseSubtotal];
    }

    public function getSourceQty($sourceCode, $sku)
    {
        $data = $this->sourceDataBySku->execute($sku);

        foreach ($data as $source) {
            if ($source['source_code'] == $sourceCode) {
                return $source['quantity'];
            }
        }
        return 0;
    }

    public function mergeArray($_item)
    {
        return array_merge([$_item], $_item->getChildrenItems());
    }
}
