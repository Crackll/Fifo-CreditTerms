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
namespace Webkul\MpPromotionCampaign\Model\Layer;
 
class Resolver extends \Magento\Catalog\Model\Layer\Resolver
{
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Webkul\MpPromotionCampaign\Model\Layer $layer,
        array $layersPool
    ) {
        $this->layer = $layer;
        parent::__construct($objectManager, $layersPool);
    }
 
    // public function create($layerType)
    // {
    // }
}
