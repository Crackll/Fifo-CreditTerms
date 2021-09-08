<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Controller\Adminhtml\Product;

use Webkul\MpWholesale\Controller\Adminhtml\Product as ProductController;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;

class Save extends ProductController
{
    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecoder;
    /**
     * @var \Webkul\MpWholesale\Helper\Data
     */
    protected $helper;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Webkul\MpWholesale\Model\Storage\ProductStorage $dbStorage
     * @param \Webkul\MpWholesale\Model\ProductFactory $mpWholeSaleProduct
     * @param \Webkul\MpWholesale\Helper\Data $helper
     * @param \Webkul\MpWholesale\Helper\Email $emailHelper
     * @param \Magento\Backend\Model\Auth\Session $authSession
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Webkul\MpWholesale\Model\Storage\ProductStorage $dbStorage,
        \Webkul\MpWholesale\Model\ProductFactory $mpWholeSaleProduct,
        \Webkul\MpWholesale\Helper\Data $helper,
        \Webkul\MpWholesale\Helper\Email $emailHelper,
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        $this->jsonDecoder = $jsonDecoder;
        $this->dbStorage = $dbStorage;
        $this->mpWholeSaleProduct = $mpWholeSaleProduct;
        $this->helper = $helper;
        $this->emailHelper = $emailHelper;
        $this->authSession = $authSession;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $wholeData = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        if (isset($wholeData['products_id'])) {
            $productIds = array_flip($this->jsonDecoder->decode($wholeData['products_id']));
            if (empty($productIds)) {
                $this->messageManager->addError(
                    __('Please select products to set rules.')
                );
                return $resultRedirect->setPath('*/*/new');
            }
        }
        $priceRule = implode(",", $wholeData['price_rule']);
        
        if (isset($wholeData['entity_id'])) {
            unset($wholeData['approve_status']);
            $wholeSaleProductModel = $this->mpWholeSaleProduct->create()->load($wholeData['entity_id']);
            $wholeData['price_rule'] = $priceRule;
            $wholeSaleProductModel->setData($wholeData);
            $wholeSaleProductModel->save();
            $this->messageManager->addSuccess(__('Product record updated successfully.'));
            return $resultRedirect->setPath('*/*/edit', ['id'=>$wholeData['entity_id']]);
        } else {
            $tempData = [];
            $approvalStatus = 1;
            if ($this->helper->isWholesalerProductApprovalRequired()) {
                $approvalStatus = 0;
            }
            foreach ($productIds as $productId) {
                $data = [
                'user_id' => $this->getCurrentUser()->getUserId(),
                'product_id' => $productId,
                'price_rule' => $priceRule,
                'min_order_qty' => $wholeData['min_order_qty'],
                'max_order_qty' => $wholeData['max_order_qty'],
                'prod_capacity' => $wholeData['prod_capacity'],
                'duration_type' => $wholeData['duration_type'],
                'status'  => $wholeData['status'],
                'approve_status' => $approvalStatus
                ];
                $tempData[] = $data;
            }
            try {
                if (!empty($tempData)) {
                    $this->dbStorage->insertMultiple($tempData);
                }
                if (!$approvalStatus) {
                    foreach ($tempData as $data) {
                        $this->emailHelper->sendNewProductMail($data);
                    }
                }
                $this->messageManager->addSuccess(__('Product record saved successfully.'));
                return $resultRedirect->setPath('*/*/index');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('*/*/index');
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * get current admin user
     * @return mixed
     */
    public function getCurrentUser()
    {
        return $this->authSession->getUser();
    }
}
