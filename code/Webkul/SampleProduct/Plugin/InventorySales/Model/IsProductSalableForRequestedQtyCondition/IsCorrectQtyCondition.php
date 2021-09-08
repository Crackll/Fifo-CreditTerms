<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SampleProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SampleProduct\Plugin\InventorySales\Model\IsProductSalableForRequestedQtyCondition;

use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;
use Magento\Framework\Phrase;
use Webkul\SampleProduct\Helper\Data as HelperData;
use Magento\Catalog\Api\ProductRepositoryInterface;

class IsCorrectQtyCondition
{
    /**
     * @var ProductSalabilityErrorInterfaceFactory
     */
    protected $productSalabilityErrorFactory;

    /**
     * @var ProductSalableResultInterfaceFactory
     */
    protected $productSalableResultFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @param ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory
     * @param ProductSalableResultInterfaceFactory   $productSalableResultFactory
     * @param HelperData                             $helperData
     * @param ProductRepositoryInterface             $productRepository
     */
    public function __construct(
        ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory,
        ProductSalableResultInterfaceFactory $productSalableResultFactory,
        ProductRepositoryInterface $productRepository,
        HelperData $helperData
    ) {
        $this->productSalabilityErrorFactory = $productSalabilityErrorFactory;
        $this->productSalableResultFactory = $productSalableResultFactory;
        $this->productRepository = $productRepository;
        $this->helperData = $helperData;
    }

    /**
     * {@inheritdoc}
     */
    public function aroundExecute(
        \Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition\IsCorrectQtyCondition $subject,
        callable $proceed,
        string $sku,
        int $stockId,
        float $requestedQty
    ) {
        if ($this->helperData->isSampleProductEnable()) {
            $product = $this->productRepository->get($sku);
            if ($this->helperData->isCurrentSampleProduct($product->getId())) {
                $allowProductLimit = $this->helperData->maxSampleQty();
                if ($allowProductLimit && $allowProductLimit != "" && $allowProductLimit < $requestedQty) {
                    return $this->createErrorResult(
                        'is_correct_qty-max_sale_qty',
                        __('The requested qty exceeds the maximum %1 qty allowed in shopping cart', $allowProductLimit)
                    );
                }
            }
        }
        return $proceed($sku, $stockId, $requestedQty);
    }

    /**
     * Create Error Result Object
     *
     * @param string $code
     * @param Phrase $message
     * @return ProductSalableResultInterface
     */
    public function createErrorResult(string $code, Phrase $message): ProductSalableResultInterface
    {
        $errors = [
            $this->productSalabilityErrorFactory->create([
                'code' => $code,
                'message' => $message
            ])
        ];
        return $this->productSalableResultFactory->create(['errors' => $errors]);
    }
}
