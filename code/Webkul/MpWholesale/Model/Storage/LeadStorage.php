<?php
/**
 * @category   Webkul
 * @package    Webkul_MpWholesale
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Model\Storage;

use Magento\Framework\App\ResourceConnection;
use Webkul\MpWholesale\Model\ProductFactory;
use Webkul\MpWholesale\Model\LeadsFactory;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Magento\Catalog\Model\ProductFactory as ProductModel;

class LeadStorage
{
    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var LeadsFactory
     */
    private $leadsFactory;

    /**
     * @var MpHelper
     */
    private $mpHelper;

    /**
     * @var ProductModel
     */
    private $productModel;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @param ProductFactory $productFactory
     * @param LeadsFactory $leadsFactory
     * @param MpHelper $mpHelper
     * @param ProductModel $productModel
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Psr\Log\LoggerInterface $loggerInterface
     */
    public function __construct(
        ProductFactory $productFactory,
        LeadsFactory $leadsFactory,
        MpHelper $mpHelper,
        ProductModel $productModel,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Psr\Log\LoggerInterface $loggerInterface
    ) {
    
        $this->productFactory = $productFactory;
        $this->leadsFactory = $leadsFactory;
        $this->mpHelper = $mpHelper;
        $this->productModel = $productModel;
        $this->date = $date;
        $this->loggerInterface = $loggerInterface;
    }

    /**
     * saveLeadData
     * @param int $productId
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Exception
     */
    public function saveLeadData($productId = 0)
    {
        try {
            $sellerId = $this->mpHelper->getCustomerId();
            $productModel = $this->productModel->create()->load($productId);
            $productName = $productModel->getName();
            $collection = $this->productFactory->create()
                                ->getCollection()
                                ->addFieldToFilter('product_id', $productId)
                                ->addFieldToFilter('status', 1);
            $wholeSalerIds = $collection->getAllWholeSalerIds();
            foreach ($wholeSalerIds as $wholesalerId) {
                $leadsCollection = $this->leadsFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter('product_id', $productId)
                                    ->addFieldToFilter('seller_id', $sellerId)
                                    ->addFieldToFilter('wholesaler_id', $wholesalerId);
                if ($leadsCollection->getSize()) {
                    foreach ($leadsCollection as $leadsData) {
                        $count = $leadsData->getViewCount() + 1;
                        $leadsData->setViewCount($count);
                        $leadsData->setRecentViewAt($this->date->gmtDate());
                        $leadsData->save();
                    }
                } else {
                    $data = [
                        'seller_id' => $sellerId,
                        'wholesaler_id' => $wholesalerId,
                        'product_name'  => $productName,
                        'product_id'    => $productId,
                        'view_count'    => 1,
                        'status'        => 1,
                        'view_at'       => $this->date->gmtDate(),
                        'recent_view_at'=> $this->date->gmtDate()
                    ];
                    $leadModel = $this->leadsFactory->create();
                    $leadModel->setData($data);
                    $leadModel->save();
                }
            }
        } catch (\Exception $e) {
            $this->loggerInterface->info($e->getMessage());
        }
    }
}
