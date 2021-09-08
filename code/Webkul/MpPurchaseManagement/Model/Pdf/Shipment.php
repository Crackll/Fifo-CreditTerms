<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPurchaseManagement\Model\Pdf;

/**
 * Shipment PDF model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Shipment extends \Magento\Sales\Model\Order\Pdf\AbstractPdf
{
    /**
     * @var \Webkul\MpPurchaseManagement\Model\OrderItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var \Webkul\MpPurchaseManagement\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var Webkul\MpWholesale\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\MpWholesale\Model\WholeSaleUserFactory
     */
    protected $wholesalerFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customer;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $address;

    /**
     * @param \Webkul\MpPurchaseManagement\Model\OrderItemFactory  $orderItemFactory
     * @param \Webkul\MpPurchaseManagement\Model\OrderFactory      $orderFactory
     * @param \Webkul\MpWholesale\Helper\Data                      $helper
     * @param \Webkul\MpWholesale\Model\WholeSaleUserFactory       $wholesalerFactory
     * @param \Magento\Payment\Helper\Data                         $paymentData
     * @param \Magento\Framework\Stdlib\StringUtils                $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface   $scopeConfig
     * @param \Magento\Framework\Filesystem                        $filesystem
     * @param \Magento\Sales\Model\Order\Pdf\Config                $pdfConfig
     * @param \Magento\Sales\Model\Order\Pdf\Total\Factory         $pdfTotalFactory
     * @param \Magento\Sales\Model\Order\Pdf\ItemsFactory          $pdfItemsFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface   $inlineTranslation
     * @param \Magento\Sales\Model\Order\Address\Renderer          $addressRenderer
     * @param \Magento\Customer\Api\CustomerRepositoryInterface    $customer
     * @param \Magento\Customer\Api\AddressRepositoryInterface     $address
     * @param array                                                $data
     */
    public function __construct(
        \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory,
        \Webkul\MpPurchaseManagement\Model\OrderFactory $orderFactory,
        \Webkul\MpWholesale\Helper\Data $helper,
        \Webkul\MpWholesale\Model\WholeSaleUserFactory $wholesalerFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\Order\Pdf\Config $pdfConfig,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Customer\Api\CustomerRepositoryInterface $customer,
        \Magento\Customer\Api\AddressRepositoryInterface $address,
        array $data = []
    ) {
        $this->orderItemFactory = $orderItemFactory;
        $this->orderFactory = $orderFactory;
        $this->helper = $helper;
        $this->wholesalerFactory = $wholesalerFactory;
        $this->customer = $customer;
        $this->address = $address;
        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfConfig,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $addressRenderer,
            $data
        );
    }

    /**
     * Return PDF document
     *
     * @param array $shipments
     * @return \Zend_Pdf
     */
    public function getPdf($shipments = [])
    {
        $this->_beforeGetPdf();

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($shipments as $shipmentId) {
            $page = $this->newPage();
            $shipment       = $this->getShippmentCollection($shipmentId);
            $orderDetails   = $this->getOrderDetails($shipment->getPurchaseOrderId());
            /* Add image */
            $this->insertLogo($page);
            /* Add address */
            $this->insertAddress($page);
            /* Add head */
            $this->insertPurchase(
                $page,
                $orderDetails,
                $shipment
            );
            /* Add table */
            $this->_drawLineHeader($page);
            /* Draw item */
            $this->drawPurchaseItem($shipment, $page);
            $page = end($pdf->pages);
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * Purchase Order header
     * @param  \Zend_Pdf_Page
     * @param  \Webkul\MpPurchaseManagement\Model\Order
     * @param  \Webkul\MpPurchaseManagement\Model\OrderItem
     * @return void
     */
    protected function insertPurchase(&$page, $order, $shipment)
    {
        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->drawRectangle(25, $top, 570, $top - 55);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
        $this->_setFontRegular($page, 10);

        $page->drawText(
            __('Purchase Order ') . $order->getIncrementId(),
            35,
            ($top -= 20),
            'UTF-8'
        );
        $date = date_create($order->getCreatedAt());
        $page->drawText(
            __('Order Date: ') .date_format($date, "g:ia \o\\n l jS F Y"),
            35,
            ($top -= 15),
            'UTF-8'
        );
        $top -= 10;

        //wholesaler details
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 570, ($top - 25));
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        $page->drawText(__('Wholesaler Details:'), 35, ($top - 15), 'UTF-8');
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, ($top - 25), 570, $top - 75);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $this->y = $top - 40;
        $wholeSaler = $this->wholesalerFactory->create()->getCollection()
                    ->addFieldToFilter('user_id', ['eq'=>$order->getWholesalerId()])
                    ->setPageSize(1)->getFirstItem();
        $page->drawText($wholeSaler->getTitle(), 35, $this->y, 'UTF-8');
        $page->drawText($wholeSaler->getDescription(), 35, ($this->y -= 10), 'UTF-8');
        $page->drawText($wholeSaler->getAddress().',', 35, ($this->y -= 10), 'UTF-8');
        $this->y -= 15;

        //shipping address
        $customerModel = $this->customer->getById($shipment->getSellerId());
        $sellerAddress = $this->address->getById($customerModel->getDefaultShipping());
        $top = $this->y;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 570, ($top - 25));
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        $page->drawText(__('Ship To:'), 35, ($top - 15), 'UTF-8');
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, ($top - 25), 570, $top - 90);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $this->y = $top - 40;
        $streetAddress = '';
        foreach ($sellerAddress->getStreet() as $street) {
            $streetAddress .= $street.', ';
        }
        $page->drawText($this->helper->getCustomerData($shipment->getSellerId())->getName(), 35, $this->y, 'UTF-8');
        $page->drawText($streetAddress, 35, ($this->y -= 10), 'UTF-8');
        $page->drawText($sellerAddress->getCity(), 35, ($this->y -= 10), 'UTF-8');
        $page->drawText($sellerAddress->getRegion()->getRegion().', '.
                        $sellerAddress->getPostcode(), 35, ($this->y -= 10), 'UTF-8');
        $page->drawText($sellerAddress->getCountryId().', T:'.
                        $sellerAddress->getTelephone(), 35, ($this->y -= 10), 'UTF-8');
        $this->y -= 25;
    }

    /**
     * Draw table header for product items
     *
     * @param  \Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawLineHeader(&$page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y -15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_RGB(0, 0, 0));

        //columns headers
        $lines[0][] = ['text' => __('Products'), 'feed' => 100];

        $lines[0][] = ['text' => __('Qty'), 'feed' => 35];

        $lines[0][] = ['text' => __('SKU'), 'feed' => 565, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 10];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    /**
     * draw table row for product items
     * @param  \Webkul\MpPurchaseManagement\Model\OrderItem
     * @param  \Zend_Pdf_Page
     * @return void
     */
    protected function drawPurchaseItem($item, &$page)
    {
        $lines  = [];

        // draw Product name

        $lines[0] = [[
            'text' => $this->helper->getProduct($item->getProductId())->getName(),
            'feed' => 100,
        ]];

        // draw SKU
        $lines[0][] = [
            'text'  => $item->getSku(),
            'feed'  => 565,
            'align' => 'right'
        ];

        // draw QTY
        $lines[0][] = [
            'text'  => $item->getQuantity() * 1,
            'feed'  => 35,
        ];

        $lineBlock = [
            'lines'  => $lines,
            'height' => 20
        ];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $this->setPage($page);
    }

    /**
     * Get OrderItem Details
     *
     * @param int $shipmentId
     * @return array object
     */
    protected function getShippmentCollection($shipmentId)
    {
        $collection = $this->orderItemFactory->create()->load($shipmentId);
        return $collection;
    }

    /**
     * get Order Details
     *
     * @param int $purchaseId
     * @return array object
     */
    protected function getOrderDetails($purchaseId)
    {
        $details = $this->orderFactory->create()->load($purchaseId);
        return $details;
    }
}
