<?php
/**
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Controller\Product;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Url;

class GetPriceList extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelperData;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\MpWholesale\Model\ProductFactory $productFactory
     * @param \Webkul\MpWholesale\Model\UnitMappingFactory $unitMappingFactory
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelperData
     * @param \Webkul\MpWholesale\Helper\Data $wholesaleHelperData
     * @param Url $customerUrl
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpWholesale\Model\ProductFactory $productFactory,
        \Webkul\MpWholesale\Model\UnitMappingFactory $unitMappingFactory,
        \Webkul\Marketplace\Helper\Data $marketplaceHelperData,
        \Webkul\MpWholesale\Helper\Data $wholesaleHelperData,
        Url $customerUrl
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->productFactory = $productFactory;
        $this->unitMappingFactory = $unitMappingFactory;
        $this->marketplaceHelperData = $marketplaceHelperData;
        $this->wholesaleHelperData = $wholesaleHelperData;
        $this->customerUrl = $customerUrl;
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->customerUrl->getLoginUrl();
        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Wholesaler Product GetPriceList
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $helper = $this->wholesaleHelperData;
        $wholesaleProductid = isset($data['wholesaler_product_id']) ? $data['wholesaler_product_id'] : 0;
        $priceListsArr = [];
        if ($wholesaleProductid) {
            $productModel = $this->productFactory->create()->load($wholesaleProductid);
            $priceRules = $productModel->getPriceRule();
            $collection = $this->unitMappingFactory->create()
                                ->getCollection();
            $joinTable = $this->unitMappingFactory->create()
                                ->getCollection()
                                ->getTable('mpwholesale_unit_list');
            $collection->getSelect()->join(
                $joinTable.' as cgf',
                'main_table.unit_id = cgf.entity_id',
                [
                    'unit_name'=>'unit_name'
                ]
            )->where(
                'main_table.rule_id IN ('.$priceRules.')'
            );
            foreach ($collection as $priceListData) {
                $price = $helper->getformattedPrice($priceListData->getQtyPrice());
                $unit = $priceListData->getUnitName();
                $qty = $priceListData->getQty();
                $listArr['price'] = $price;
                $listArr['unit'] = $unit;
                $listArr['qty'] =  $qty;
                if ($qty > 1) {
                    $listArr['text'] = $price."/".$unit." for <= ".$qty." ".$unit;
                } else {
                    $listArr['text'] = $price."/".$unit." for = ".$qty." ".$unit;
                }
                $priceListsArr[] = $listArr;
            }
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($priceListsArr));
    }
}
