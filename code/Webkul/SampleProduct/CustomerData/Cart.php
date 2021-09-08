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

namespace Webkul\SampleProduct\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

class Cart extends \Magento\Checkout\CustomerData\Cart
{
    protected function getRecentItems()
    {
        $cartItems = [];
        if (!$this->getSummaryCount()) {
            return $cartItems;
        }

        foreach (array_reverse($this->getAllQuoteItems()) as $cartItem) {
            /* @var $item \Magento\Quote\Model\Quote\Item */
            if (!$cartItem->getProduct()->isVisibleInSiteVisibility()) {
                $product =  $cartItem->getOptionByCode('product_type') !== null
                    ? $cartItem->getOptionByCode('product_type')->getProduct()
                    : $cartItem->getProduct();

                $products = $this->catalogUrl->getRewriteByProductStore([$product->getId() => $cartItem->getStoreId()]);
                if (isset($products[$product->getId()])) {
                    if ($product->getTypeId() === 'sample') {
                        $cartItems[] = $this->itemPoolInterface->getItemData($cartItem);
                        continue;
                    }
                    $urlDataObject = new \Magento\Framework\DataObject($products[$product->getId()]);
                    $cartItem->getProduct()->setUrlDataObject($urlDataObject);
                }
            }
            $cartItems[] = $this->itemPoolInterface->getItemData($cartItem);
        }
        return $cartItems;
    }
}
