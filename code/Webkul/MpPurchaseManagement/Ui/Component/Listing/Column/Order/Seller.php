<?php
/**
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPurchaseManagement\Ui\Component\Listing\Column\Order;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class Seller extends Column
{
    /**
     * @var \Webkul\MpWholesale\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\MpPurchaseManagement\Model\OrderItemFactory
     */
    protected $orderItemFactory;

    /**
     * @param ContextInterface                                    $context
     * @param UiComponentFactory                                  $uiComponentFactory
     * @param \Webkul\MpWholesale\Helper\Data                     $helper
     * @param \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory
     * @param array                                               $components
     * @param array                                               $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Webkul\MpWholesale\Helper\Data $helper,
        \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory,
        array $components = [],
        array $data = []
    ) {
        $this->helper = $helper;
        $this->orderItemFactory = $orderItemFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $name = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['entity_id'])) {
                    $item[$name] = $this->getSellerName($item['entity_id']);
                }
            }
        }
        return $dataSource;
    }

    /**
     * get seller names from order ID
     * @param  int
     * @return string
     */
    protected function getSellerName($id)
    {
        $name = '';
        $collection = $this->orderItemFactory->create()->getCollection()
                           ->addFieldToFilter('purchase_order_id', ['eq'=>$id]);
        if ($collection->getSize()) {
            $collection->addFieldToSelect('seller_id')->getSelect()->group('seller_id');
            foreach ($collection as $model) {
                $sellerName = $this->helper->getCustomerData($model->getSellerId())->getFirstname();
                $name .= $sellerName.', ';
            }
        }
        return rtrim($name, ', ');
    }
}
