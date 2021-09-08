<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPromotionCampaign
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPromotionCampaign\Ui\DataProvider\Seller;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Webkul\MpPromotionCampaign\Model\ResourceModel\Campaign\Grid\CollectionFactory as SellerCampaignCollection;
use Webkul\Marketplace\Helper\Data as HelperData;

class PromotionCampaign extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
{
    /**
     * Product collection
     *
     * @var \Webkul\Marketplace\Model\ResourceModel\Product\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param SellerCampaignCollection $productCollection
     * @param \Magento\Framework\Registry $registry
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ProductCollection $productCollection,
        SellerCampaignCollection $campaignCollection,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\MpPromotionCampaign\Model\CampaignProductFactory $campaignProduct,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Webkul\MpPromotionCampaign\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        HelperData $helperData,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $productCollection,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );
        $sellerId = $helperData->getCustomerId();
        $campaignId = '';
        $campaignId =  $request->getParam('id');
        if (empty($campaignId)) {
            $url = $redirect->getRefererUrl();
            $urlData = $helper->getCampaignIdFromUrl($url);
            $campaignId = $urlData;
        }
        /** @var Collection $collection */
        $collectionData = $campaignCollection->create()->addFieldToFilter('main_table.status', '1');
        $this->collection = $collectionData;
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }
}
