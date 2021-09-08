<?php
/**
 * Webkul Marketplace DailyDeals CatalogProductSaveBefore Observer.
 * @category  Webkul
 * @package   Webkul_MpDailyDeals
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpDailyDeal\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\RequestInterface;
use Webkul\MpDailyDeal\Helper\Data as HelperData;

class PreDispatchObserver implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var \Webkul\MpDailyDeals\Helper\Data
     */
    protected $helperData;

    /**
     * @param ProductRepositoryInterface $productRepository,
     * @param ScopeConfigInterface $scopeInterface,
     * @param Cart $cart,
     * @param RequestInterface $request,
     * @param HelperData $helperData
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ScopeConfigInterface $scopeInterface,
        Cart $cart,
        RequestInterface $request,
        HelperData $helperData
    ) {
        $this->productRepository = $productRepository;
        $this->scopeConfig = $scopeInterface;
        $this->cart = $cart;
        $this->helperData = $helperData;
        $this->request = $request;
    }

    /**
     * product save event handler.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $routName = $this->request->getRouteName();
        $actionName = $this->request->getFullActionName();
        $params = $this->request->getParams();
        $updateQuote = false;
        if (!isset($params['key'])) {
            if ($routName != 'adminhtml' && strpos($actionName, 'mpquotesystem') === false) {
                $items = $this->cart->getQuote()->getAllVisibleItems();
                $modEnable = $this->scopeConfig->getValue('mpdailydeals/general/enable');
                foreach ($items as $item) {
                    if ($item->getProductType()=="configurable") {
                        continue;
                    }
                    $product = $this->productRepository->getById($item->getProductId());
                    $dealStatus = $this->helperData->getProductDealDetail($product);
                    $proDealStatus = $product->getDealStatus();
                    $customOptionPrice = $this->getCustomOptionPrice($item->getProduct());
                    $price = $this->helperData->getConvertedAmount($product->getPrice() + $customOptionPrice);
                    $specialPrice = $this->helperData
                                ->getConvertedAmount(
                                    $product->getSpecialPrice()
                                    + $customOptionPrice
                                );
                    if ($dealStatus === false && $modEnable && $proDealStatus && $item->getPrice() != $price) {
                        $item->setPrice($price);
                        $item->setOriginalCustomPrice($price);
                        $item->setCustomPrice($price);
                        $item->getProduct()->setIsSuperMode(true);
                        $updateQuote = true;
                    } elseif (is_array($dealStatus) && count($dealStatus)>1 && $modEnable && $proDealStatus && $item->getPrice() != $specialPrice) {
                        $item->setPrice($specialPrice);
                        $item->setOriginalCustomPrice($specialPrice);
                        $item->setCustomPrice($specialPrice);
                        $item->getProduct()->setIsSuperMode(true);
                        $updateQuote = true;
                    }
                }
                if ($actionName != 'checkout_index_index' && $updateQuote) {
                    $this->cart->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
                }
            }
        }
        return $this;
    }
    public function getCustomOptionPrice($product)
    {
        $finalPrice = 0;
        $optionIds = $product->getCustomOption('option_ids');
        if ($optionIds) {
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $product->getOptionById($optionId)) {
                    $confItemOption = $product->getCustomOption('option_' . $option->getId());

                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setConfigurationItemOption($confItemOption);
                    $finalPrice += $group->getOptionPrice($confItemOption->getValue(), 0);
                }
            }
        }
        return $finalPrice;
    }
}
