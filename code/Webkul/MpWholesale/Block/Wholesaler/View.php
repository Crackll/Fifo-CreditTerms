<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Block\Wholesaler;


use Webkul\MpWholesale\Model\ProductFactory as MpWholesaleProductFactory;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Block\Product\Context
     */
    protected $context;

    /**
     * @var \Webkul\MpWholesale\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productModel;

    /**
     * @var \Webkul\MpWholesale\Helper\Data
     */
    protected $wholeSaleHelper;

   /**
    * @param \Magento\Catalog\Block\Product\Context $context
    * @param \Webkul\MpWholesale\Model\ProductFactory $productFactory
    * @param \Magento\Catalog\Model\ProductFactory $productModel
    * @param \Webkul\MpWholesale\Helper\Data $wholeSaleHelper
    * @param array $data
    */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Webkul\MpWholesale\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ProductFactory $productModel,
        \Webkul\MpWholesale\Helper\Data $wholeSaleHelper,
        MpWholesaleProductFactory $mpWholeSaleProductFactory,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->productModel = $productModel;
        $this->wholeSaleHelper = $wholeSaleHelper;
        $this->mpWholeSaleProductFactory = $mpWholeSaleProductFactory;
        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        return $this;
    }

    /**
     * Get WholeSalerList By productId
     *
     * @return object
     */
    public function getWholeSalerList()
    {
        $productId = $this->getRequest()->getParam('id');
        $paramData = $this->getRequest()->getParams();
        $filter = '';
        if (isset($paramData['s'])) {
            $filter = $paramData['s'] != '' ? $paramData['s'] : '';
        }
        $mpWholeSaleProductCollection = $this->mpWholeSaleProductFactory->create()->getCollection();
        $adminUserTable = $mpWholeSaleProductCollection->getTable('admin_user');
        
        $mpWholeSaleProductCollection->getSelect()->join(
            ['aut' => $adminUserTable],
            "main_table.user_id = aut.user_id",
            [
                'wholesaler_name'=>'CONCAT(aut.firstname, " ",aut.lastname)'
            ]
        )->where('firstname like "%'.$filter.'%" OR lastname like "%'.$filter.'%"');
        
        $mpWholeSaleProductCollection->getSelect()->where(
            'main_table.product_id ='.$productId.' AND
            aut.is_active = 1 AND
            main_table.approve_status = 1 AND
            main_table.status = 1'
        );
        
        return $mpWholeSaleProductCollection;

    }

    /**
     * Get ProductName by Id
     *
     * @param integer $productId
     * @return string
     */
    public function getProductName($productId = 0)
    {
        $productModel = $this->productModel->create()
                        ->load($productId);
        return $productModel->getName();
    }

    /**
     * Get Duration of product delivered
     *
     * @param string $typeValue
     * @return string
     */
    public function getDurationType($typeValue)
    {
        $durationTypes =['d'=> __('Day'), 'w'=> __('Week'), 'm'=> __('Month'), 'y'=> __('Year')];
        return $durationTypes[$typeValue];
    }

    /**
     * Get WholeSale Helper object
     *
     * @return object
     */
    public function getWholeSaleHelper()
    {
        return $this->wholeSaleHelper;
    }
    
    /**
     * Get Form Action URL
     *
     * @param int $id
     * @return string
     */
    public function getFormActionUrl($id)
    {
        $actionUrl = $this->getUrl('mpwholesale/wholesaler/view', [
            '_secure' => $this->getRequest()->isSecure(),
            'id'=> $id
        ]);

        return $actionUrl;
    }
}
