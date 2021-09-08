<?php
/**
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\ResourceConnection;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Webkul\MpWholesale\Api\PriceRuleRepositoryInterface;
use Webkul\MpWholesale\Api\Data\PriceRuleInterfaceFactory;
use Webkul\MpWholesale\Model\WholeSalerUnitFactory;
use Webkul\MpWholesale\Model\UnitMappingFactory;
use Webkul\MpWholesale\Model\PriceRuleFactory;

abstract class Pricerule extends \Magento\Backend\App\AbstractAction
{
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        DateTime $date,
        ResourceConnection $resource,
        Session $authSession,
        ForwardFactory $resultForwardFactory,
        PriceRuleRepositoryInterface $priceRuleRepositoryInterface,
        PriceRuleInterfaceFactory $piceRuleInterface,
        WholeSalerUnitFactory $wholeSalerUnitFactory,
        UnitMappingFactory $unitMappingModel,
        PriceRuleFactory $priceRuleModel
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->date = $date;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->authSession = $authSession;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->priceRuleRepositoryInterface = $priceRuleRepositoryInterface;
        $this->piceRuleInterface = $piceRuleInterface;
        $this->wholeSalerUnitFactory = $wholeSalerUnitFactory;
        $this->unitMappingModel = $unitMappingModel;
        $this->priceRuleModel = $priceRuleModel;
    }
    
    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Webkul_MpWholesale::menu');
        return $this;
    }
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpWholesale::pricerule');
    }
}
