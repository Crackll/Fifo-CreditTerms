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

namespace Webkul\MpMSI\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Webkul\Marketplace\Helper\Data as HelperData;

/**
 * Webkul Marketplace SaveInventorySource Observer.
 */
class SaveInventorySource implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\InventoryCatalogAdminUi\Observer\SourceItemsProcessor
     */
    protected $sourceItemsProcessor;

    /**
     * \Magento\Store\Model\App\Emulation
     */
    protected $emulation;

    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * @param ManagerInterface $messageManager
     * @param \Magento\InventoryCatalogAdminUi\Observer\SourceItemsProcessor $sourceItemsProcessor
     * @param \Magento\Store\Model\App\Emulation $emulation
     * @param HelperData $helper
     */
    public function __construct(
        ManagerInterface $messageManager,
        \Magento\InventoryCatalog\Model\SourceItemsProcessor $sourceItemsProcessor,
        \Magento\InventoryCatalog\Model\IsSingleSourceMode $singleSourceMode,
        \Magento\Store\Model\App\Emulation $emulation,
        HelperData $helper
    ) {
        $this->messageManager = $messageManager;
        $this->sourceItemsProcessor = $sourceItemsProcessor;
        $this->singleSourceMode = $singleSourceMode;
        $this->emulation = $emulation;
        $this->helper = $helper;
    }

    /**
     * Checkout event handler for saving sources
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->singleSourceMode->execute()) {
            try {
                $this->emulation->startEnvironmentEmulation(0, 'adminhtml');
                $productData = $observer->getEvent()->getData();
                //save sources for simple products
                if (isset($productData[0]["product"]["sku"]) && $productData[0]["product"]["sku"]) {
                    $sku = $productData[0]["product"]["sku"];
                    $sources = [];
                    if (isset($productData[0]["sources"])) {
                        $sources = $productData[0]["sources"]["assigned_sources"];
                        $this->sourceItemsProcessor->execute(
                            $sku,
                            $sources
                        );
                    } else {
                        $this->sourceItemsProcessor->execute(
                            $sku,
                            $sources
                        );
                    }
                }
                
                //save sources for configurable products
                if (isset($productData[0]["configurations"]) || $productData[0]["variations-matrix"]) {
                    $associateProducts = [];

                    if (isset($productData[0]["configurations"])) {
                        $associateProducts =  array_merge($associateProducts, $productData[0]["configurations"]);
                    }

                    if (isset($productData[0]["variations-matrix"])) {
                        $associateProducts = array_merge($associateProducts, $productData[0]["variations-matrix"]);
                    }
                    $this->getSourceItemsProcessor($associateProducts);
                }
                $this->emulation->stopEnvironmentEmulation($associateProducts);
            } catch (\Exception $e) {
                $this->helper->logDataInLogger(
                    "SaveInventorySource Observer execute : ".$e->getMessage()
                );
            } finally {
                $this->emulation->stopEnvironmentEmulation();
            }
        }
    }

    //save sources for configurable products
    public function getSourceItemsProcessor($associateProducts)
    {
        foreach ($associateProducts as $associateProduct) {
                    
            if (isset($associateProduct['sku']) && $associateProduct['sku']) {
                $sources = isset(
                    $associateProduct[
                        'quantity_per_source'
                    ]
                )
                ?json_decode(
                    $associateProduct['quantity_per_source'],
                    true
                ):null;
                if ($sources) {
                    $sourcesForSave = [];
                    foreach ($sources as $key => $source) {
                        array_push($sourcesForSave, [
                            "source_code" => $sources[$key]["source_code"],
                            "status" => 1,
                            "quantity" => $sources[$key]["quantity"]
                        ]);
                    }
            
                    $this->sourceItemsProcessor->execute(
                        $associateProduct['sku'],
                        $sourcesForSave
                    );
                }
            }
        }
    }
}
