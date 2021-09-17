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
namespace Webkul\MpPromotionCampaign\Block\Promotion\LayeredNavigation\Navigation;

class State extends \Magento\LayeredNavigation\Block\Navigation\State
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\MpPromotionCampaign\Model\Layer\Resolver $layerResolver
     * @param \Webkul\MpPromotionCampaign\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpPromotionCampaign\Model\Layer\Resolver $layerResolver,
        \Webkul\MpPromotionCampaign\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $layerResolver, $data);
    }
}
