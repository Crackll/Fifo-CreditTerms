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
namespace Webkul\MpPromotionCampaign\Block\Navigation;
 
class State extends \Magento\LayeredNavigation\Block\Navigation\State
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpPromotionCampaign\Model\Layer\Resolver $layerResolver,
        \Webkul\MpPromotionCampaign\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $layerResolver, $data);
    }
}
