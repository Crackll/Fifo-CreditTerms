<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRewardSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRewardSystem\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Serialize\SerializerInterface;

class RewardsConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;
    /**
     * @var SessionManager
     */
    protected $session;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;
    /**
     * @var Session
     */
    protected $cart;
    /**
     * @var RewardrecordFactory
     */
    protected $rewardRecord;
    /**
     * @var \Magento\Quote\Model\Quote\Item\OptionFactory
     */
    protected $quoteOptionFactory;
    /**
     * @var \Webkul\Marketplace\Model\ProductFactory
     */
    protected $marketplaceProductFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpRewardHelper;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializable;
    /**
     * Undocumented function
     *
     * @param CurrentCustomer $currentCustomer
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param RewardrecordFactory $rewardRecord
     * @param SessionManager $session
     * @param PriceCurrencyInterface $priceCurrency
     * @param Session $cart
     * @param \Magento\Quote\Model\Quote\Item\OptionFactory $quoteOptionFactory
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\MpRewardSystem\Helper\Data $mpRewardHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $serializable
     * @param SerializerInterface $serializer
     * @param \Webkul\Marketplace\Model\ProductFactory $marketplaceProductFactory
     */
    public function __construct(
        CurrentCustomer $currentCustomer,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        RewardrecordFactory $rewardRecord,
        SessionManager $session,
        PriceCurrencyInterface $priceCurrency,
        Session $cart,
        \Magento\Quote\Model\Quote\Item\OptionFactory $quoteOptionFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MpRewardSystem\Helper\Data $mpRewardHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Serialize\Serializer\Json $serializable,
        SerializerInterface $serializer,
        \Webkul\Marketplace\Model\ProductFactory $marketplaceProductFactory
    ) {
        $this->objectManager = $objectManager;
        $this->currentCustomer = $currentCustomer;
        $this->session = $session;
        $this->rewardRecord = $rewardRecord;
        $this->priceCurrency = $priceCurrency;
        $this->cart = $cart;
        $this->serializable = $serializable;
        $this->quoteOptionFactory = $quoteOptionFactory;
        $this->mpHelper = $mpHelper;
        $this->mpRewardHelper = $mpRewardHelper;
        $this->jsonHelper = $jsonHelper;
        $this->serializer = $serializer;
        $this->marketplaceProductFactory = $marketplaceProductFactory;
    }

    /**
     * set data in window.checkout.config for checkout page.
     *
     * @return array $options
     */
    public function getConfig()
    {
        $options = [
            'rewards' => [],
            'rewardSession' => [],
            'rewardStatus' => $this->mpRewardHelper->getMpRewardEnabled(),
        ];
        $cart = $this->cart->getQuote();
        $store = $cart->getStore();
        /** create array of reward session to get data on checkout page */
        if (is_array($this->session->getRewardInfo())) {
            $options['rewardSession'] = $this->session->getRewardInfo();
            foreach ($options['rewardSession'] as $key => $value) {
                $options['rewardSession'][$key]['base_amount'] = $options['rewardSession'][$key]['amount'];
                $amountPrice = $this->priceCurrency->convert(
                    $options['rewardSession'][$key]['amount'],
                    $store
                );
                $availAmount = $this->priceCurrency->convert(
                    $options['rewardSession'][$key]['avail_amount'],
                    $store
                );
                $options['rewardSession'][$key]['amount'] = $amountPrice;
                $options['rewardSession'][$key]['avail_amount'] = $availAmount;
            }
        }
        $customerId = $this->currentCustomer->getCustomerId();
        $sellerIds = [];
        $sellerId = 0;
        foreach ($cart->getAllVisibleItems() as $item) {
            $mpassignproductId = $this->getAssignProduct($item);
            $sellerId = $this->getSellerId($mpassignproductId, $item->getProductId());
            if ($mpassignproductId > 0) {
                $sellerIds[] = $sellerId;
            } else {
                $productId = $item->getProduct()->getId();
                /** get seller id from marketplace helper */
                $model = $this->mpHelper->getSellerProductDataByProductId($productId);
                if ($model->getSize()) {
                    foreach ($model as $value) {
                        $sellerIds[] = $value->getSellerId();
                    }
                } else {
                    $sellerIds[] = 0;
                }
            }
        }
        $collection = $this->rewardRecord->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $customerId])
            ->addFieldToFilter('seller_id', ['in' => $sellerIds]);
        foreach ($collection as $info) {
            if (!isset($options['rewards'][$info->getSellerId()])) {
                $options['rewards'][$info->getSellerId()] = [];
                $remainingRewards = $info->getRemainingRewardPoint();
            } else {
                $remainingRewards = $options['rewards'][$info->getSellerId()]['remaining_reward_point'] +
                $info->getRemainingRewardPoint();
            }
            $pricePerReward = $this->mpRewardHelper->getRewardValue($info->getSellerId());
            $amount = $remainingRewards * $pricePerReward;
            /** conver currency according to store */
            $amountPrice = $this->priceCurrency->convert(
                $amount,
                $store
            );
            $pricePerReward = $this->priceCurrency->convert(
                $pricePerReward,
                $store
            );
            /** create array of rewards according to seller for display on checkout page */
            $options['rewards'][$info->getSellerId()]['remaining_reward_point'] = $remainingRewards;

            $options['rewards'][$info->getSellerId()]['amount'] = $remainingRewards * $pricePerReward;

            $options['rewards'][$info->getSellerId()]['seller_id'] = $info->getSellerId();
            $options['rewards'][$info->getSellerId()]['seller_text'] = 'Seller: ' .
            $this->mpRewardHelper->getSellerName($info->getSellerId()) .
            ', Rewards: ' . $remainingRewards .
            ', Amount: ' .
            $this->priceCurrency->getCurrencySymbol($store) . $amountPrice;
        }

        foreach ($sellerIds as $sellerId) {
            if (isset($options['rewards'][$sellerId])
             && $options['rewards'][$sellerId]['remaining_reward_point'] == 0) {
                unset($options['rewards'][$sellerId]);
            }
        }

        return $options;
    }
    /**
     * get the assign product to seller
     *
     * @param $item
     * @return assign product id
     */
    public function getAssignProduct($item)
    {
        $mpassignproductId = 0;
        $itemOption = $this->quoteOptionFactory->create()
            ->getCollection();

        $itemOption = $itemOption->addFieldToFilter('item_id', ['eq' => $item->getId()])
            ->addFieldToFilter('code', ['eq' => 'info_buyRequest']);
        $optionValue = '';

        if ($itemOption->getSize()) {
            foreach ($itemOption as $value) {
                $optionValue = $value->getValue();
            }
        }
        if ($optionValue != '') {
            $temp = [];
            if ($this->validJson($optionValue)) {
                $temp = $this->jsonHelper->jsonDecode($optionValue);
            } else {
                $temp = $this->serializer->unserialize($optionValue);
            }
            $mpassignproductId = isset($temp['mpassignproduct_id']) ? $temp['mpassignproduct_id'] : 0;
        }

        return $mpassignproductId;
    }
    /**
     * get the seller id
     *
     * @param $mpassignproductId
     * @param $proid
     * @return $sellerId
     */
    public function getSellerId($mpassignproductId, $proid)
    {
        $sellerId = 0;
        if ($mpassignproductId) {
            $mpassignModel = $this->objectManager
                ->create(\Webkul\MpAssignProduct\Model\Items::class)
                ->load($mpassignproductId);
            $sellerId = $mpassignModel->getSellerId();
        } else {
            $collection = $this->marketplaceProductFactory->create()
                ->getCollection()
                ->addFieldToFilter('mageproduct_id', ['eq' => $proid]);
            foreach ($collection as $temp) {
                $sellerId = $temp->getSellerId();
            }
        }

        return $sellerId;
    }
    /**
     * get json
     *
     * @param $string
     * @return json
     */
    public function validJson($string)
    {
        $result = $this->serializable->unserialize($string);
        return $result;
    }
}
