<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c)   Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvertisementManager\Controller\Advertise;

use Magento\Framework\Exception\StateException;

class AddToCart extends \Webkul\MpAdvertisementManager\Controller\AbstractAds
{
    const PRODUCT_SKU = "wk_mp_ads_plan";

    /**
     * execute add ad plans to the cart
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $customerId = $this->_getSession()->getId();
        $blockId = $this->getRequest()->getParam("blockId");
        $formKey = $this->getRequest()->getParam("form_key");
        $wholedata = $this->getRequest()->getParam('book');
        $settings = $this->_helper->getSettingsById($blockId);
        $positionsCount =  isset($settings['sort_order'])?$settings['sort_order']:1;
        $bookedPositions = $this->_orderHelper->getBookedAdsCount($blockId);

        if (isset($wholedata[$blockId]['block']) && $wholedata[$blockId]['block']) {
            if ($bookedPositions >= $positionsCount) {
                $this->messageManager->addError(__("this ad position is already max sold out, please book other"));
                $this->_redirect("mpads/advertise/");
            } else {
                $wholedata[$blockId]['days'] = isset($settings['valid_for'])?$settings['valid_for']:1;

                $option = $this->createCustomOption($wholedata, $blockId);
                $additionalOptions = $this->createAdditionalOptions($wholedata, $blockId);
                $planProduct = $this->productRepository->get(self::PRODUCT_SKU);
                $planProduct->addCustomOption('additional_options', $additionalOptions);
                $quoteId = $this->_cartManager->createEmptyCartForCustomer($customerId);

                $cart = $this->_cartItemManager->getList($quoteId);
                $itemId = 0;
                $flag = 0;
                if (count($cart) > 0) {
                    foreach ($cart as $cartItem) {
                        if ($cartItem->getOptionByCode('block_id_'.$blockId)) {
                            $itemId = $cartItem->getId();
                            $this->messageManager->addWarning(__("item already added to the cart"));
                            $flag = 1;
                        }
                    }
                }
                if ($flag == 1) {
                    $this->_redirect($this->getRedirectUrl());
                    return $resultPage;
                }
                if ($itemId) {
                    $this->_cartDataItem->setItemId($itemId);
                }
                $quote = $this->_quoteFactory->create()->load($quoteId);
                $this->_cartDataItem->setSku($planProduct->getSku());
                $this->_cartDataItem->setProductId($planProduct->getId());
                $this->_cartDataItem->setQuote($quote);
                $this->_cartDataItem->setQuoteId($quoteId);
                $this->_cartDataItem->setQty(1);
                $this->_cartDataItem->setName($planProduct->getName());
                $this->_cartDataItem->setProductType($planProduct->getTypeId());
                $this->_cartDataItem->setPrice($wholedata[$blockId]['price']);

                try {
                    $item = $this->_cartItemManager->save($this->_cartDataItem);
                    $item->setCustomPrice($wholedata[$blockId]['price']);
                    $item->setOriginalCustomPrice($wholedata[$blockId]['price']);
                    $item->addOption(['code'=>'info_buyRequest', 'value'=> $this->_serializer->serialize($wholedata)]);
                    $item->addOption(['code'=>'block_id_'.$blockId, 'value'=> $this->_serializer->serialize($option)]);
                    $item->save();
                    $this->cartModel->save();
                    $this->messageManager->addSuccess(__("Item added to the cart successfully"));
                    $this->_redirect($this->getRedirectUrl());
                } catch (\Exception $e) {
                    $this->messageManager->addWarning(__("unable to add product to the cart"));
                    $this->_redirect("mpads/advertise/");
                }
            }
        } else {
            $this->messageManager->addError(__("no ad block selected"));
            $this->_redirect("mpads/advertise/");
        }

        /**
         * @var \Magento\Framework\View\Result\Page $resultPage
         */
        return $resultPage;
    }

    /**
     * createCustomOption create custom option
     *
     * @param  array $wholedata
     * @param  int   $blockId
     * @return array
     */
    public function createCustomOption($wholedata, $blockId)
    {
        return ['code'=>'block_id_'.$blockId, 'value'=> $this->_serializer->serialize($wholedata)];
    }

    /**
     * createAdditionalOptions additional data to show on cart page
     *
     * @param  array $options
     * @param  int   $blockId
     * @return array
     */
    public function createAdditionalOptions($options, $blockId)
    {
        if (isset($options[$blockId])) {
            $values =  $options[$blockId];
            $addOptions = [];
            array_push($addOptions, ['label' => __('Ad Position'), 'value'
            => $this->_helper->getPositionLabel($values['block_position'])]);
            array_push($addOptions, ['label'
            => __('Valid Days'), 'value' => $values['days']]);
            array_push($addOptions, ['label' => __('Block'), 'value'
            => $this->_helper->getBlockLabel($values['block'])]);
            return $this->_serializer->serialize($addOptions);
        } else {
            throw new StateException(__("unable to add the product in cart"));
        }
    }

    /**
     * getRedirectUrl function
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $isRedirectToShoppingCart = $this->_scopeConfig->getValue('checkout/cart/redirect_to_cart', $storeScope);
        if ($isRedirectToShoppingCart) {
            return "checkout/cart/";
        } else {
            return "mpads/advertise/";
        }
    }
}
