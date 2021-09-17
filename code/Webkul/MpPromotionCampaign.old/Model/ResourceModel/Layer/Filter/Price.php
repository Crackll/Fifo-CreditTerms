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
namespace Webkul\MpPromotionCampaign\Model\ResourceModel\Layer\Filter;
 
use Magento\Framework\App\Http\Context;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
 
class Price extends \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price
{
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Webkul\MpPromotionCampaign\Model\Layer\Resolver $layerResolver,
        \Magento\Customer\Model\Session $session,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connectionName = null,
        IndexScopeResolverInterface $priceTableResolver = null,
        Context $httpContext = null
    ) {
        parent::__construct($context, $eventManager, $layerResolver, $session, $storeManager, $connectionName);
    }
}
