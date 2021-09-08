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
namespace Webkul\MpMSI\Helper;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Directory\Model\CurrencyConfig;
use Magento\Directory\Helper\Data as DirectoryHelper;

/**
 * MpMSI helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    /**
     * @var \Magento\InventoryCatalog\Model\IsSingleSourceMode
     */
    protected $singleSourceMode;

    /**
     * @var SourceRepositoryInterface
     */
    protected $sourceRepository;

    /**
     * @var \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku
     */
    protected $salableQtyBySku;

    /**
     * @var \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku
     */
    protected $sourceDataBySkul;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\InventoryCatalog\Model\IsSingleSourceMode $singleSourceMode
     * @param \Magento\InventoryApi\Api\SourceRepositoryInterface $sourceRepository
     * @param \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku $sourceDataBySku
     * @param \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku $salableQtyBySku
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\InventoryCatalog\Model\IsSingleSourceMode $singleSourceMode,
        \Magento\InventoryApi\Api\SourceRepositoryInterface $sourceRepository,
        \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku $sourceDataBySku,
        \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku $salableQtyBySku,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        parent::__construct($context);
        $this->singleSourceMode = $singleSourceMode;
        $this->sourceRepository = $sourceRepository;
        $this->salableQtyBySku = $salableQtyBySku;
        $this->sourceDataBySku = $sourceDataBySku;
        $this->_productRepository = $productRepository;
    }

    /**
     * get if single source mode is enable
     *
     * @return boolean
     */
    public function isSingleStoreMode()
    {
        return $this->singleSourceMode->execute();
    }

    /**
     * Get source name by code
     *
     * @param $sourceCode
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSourceName(string $sourceCode): string
    {
        return $this->sourceRepository->get($sourceCode)->getName();
    }

    /**
     * get source qty by sku
     * @param string $sku
     *
     * @return []
     */
    public function getSourceQtyBySku($sku)
    {
        return  $this->sourceDataBySku->execute($sku);
    }

    /**
     * get salable qty by sku
     * @param string $sku
     *
     * @return []
     */
    public function getSalableQtyBySku($sku)
    {
        return  $this->salableQtyBySku->execute($sku);
    }

    /**
     * Get Product
     *
     * @param [type] $id
     * @return void
     */
    public function getProductById($id)
    {
        return $this->_productRepository->getById($id);
    }
}
