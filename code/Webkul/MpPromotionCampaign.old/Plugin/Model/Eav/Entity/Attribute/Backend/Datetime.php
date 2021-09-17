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
namespace Webkul\MpPromotionCampaign\Plugin\Model\Eav\Entity\Attribute\Backend;

use Webkul\MpPromotionCampaign\Model\CampaignFactory;
use Webkul\MpPromotionCampaign\Model\ResourceModel\CampaignProduct\CollectionFactory;

class Datetime
{
    private $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param CampaignFactory $campaign
     * @param CollectionFactory $campaignProduct
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        CampaignFactory $campaign,
        CollectionFactory $campaignProduct
    ) {
        $this->logger = $logger;
        $this->campaign = $campaign;
        $this->campaignProduct = $campaignProduct;
    }
    /**
     * After beforeSave
     *
     * @param \Magento\Eav\Model\Entity\Attribute\Backend\Datetime $subject
     * @param $result
     * @param \Magento\Framework\DataObject $entity
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return $result
     */
    public function afterBeforeSave(
        \Magento\Eav\Model\Entity\Attribute\Backend\Datetime $subject,
        $result,
        \Magento\Framework\DataObject $entity
    ) {
        $attributeName = $result->getAttribute()->getName();
        if ($attributeName == 'special_from_date' || $attributeName == 'special_to_date') {
            $productId = $entity->getData('entity_id');
            $campaignProduct = $this->campaignProduct->create()
                                ->addFieldToFilter('product_id', $productId);
            foreach ($campaignProduct as $camProduct) {
                $campaignId = $camProduct->getCampaignId();
                $campaignModel = $this->campaign->create()->load($campaignId);
                if ($attributeName == 'special_from_date') {
                    if ($campaignModel->getId()) {
                        $campaign = $campaignModel->getData();
                        $startTime = $campaign['start_date'];
                        $promotionPrice = $camProduct->getPrice();
                    } else {
                        $startTime = '';
                        $promotionPrice = null;
                    }
                    $entity->setData($attributeName, $startTime);
                    $entity->setData($attributeName . '_is_formated', true);
                    // set special price as promotion campaign price
                    $entity->setData('special_price', $promotionPrice);
                } elseif ($attributeName == 'special_to_date') {
                    if ($campaignModel->getId()) {
                        $campaign = $campaignModel->getData();
                        $endTime = $campaign['end_date'];
                        $promotionPrice = $camProduct->getPrice();
                    } else {
                        $endTime = '';
                        $promotionPrice = null;
                    }
                    $entity->setData($attributeName, $endTime);
                    $entity->setData($attributeName . '_is_formated', true);
                }
            }
        }
        return $result;
    }
}
