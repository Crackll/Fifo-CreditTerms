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

namespace Webkul\MpWholesale\Controller\Adminhtml\Pricerule;

use Webkul\MpWholesale\Controller\Adminhtml\Pricerule as PriceruleController;

class Save extends PriceruleController
{
    /**
     * WholeSale Unit mapped With Product table
     */
    const TABLE_NAME = 'mpwholesale_unit_mapping';

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $userId = $this->getCurrentUser()->getUserId();
            $data['user_id'] = $userId;
            $data['created_date'] = $this->date->gmtDate();
            $priceRuleModel = $this->priceRuleModel->create();
            try {
                if (isset($data['entity_id'])) {
                    $model = $priceRuleModel->load($data['entity_id']);
                    $model->setRuleName($data['rule_name']);
                    $model->setStatus($data['status']);
                    $model->save();
                } else {
                    $model = $priceRuleModel->setData($data)->save();
                }
                if ($model->getId() && isset($data['option'])) {
                    $this->saveUnitMapping($data, $model->getEntityId());
                }
            } catch (\Exception $e) {
                $this->messageManager->addSuccess(__($e->getMessage()));
            }
            if (isset($data['entity_id'])) {
                $this->messageManager->addSuccess(__('Price Rule Details Successfully Updated'));
            } else {
                $this->messageManager->addSuccess(__('Price Rule Details Successfully Saved'));
            }
        }
        return $resultRedirect->setPath('*/*/index');
    }

    /**
     * get current admin user
     * @return mixed
     */
    public function getCurrentUser()
    {
        return $this->authSession->getUser();
    }

    private function saveUnitMapping($data, $ruleId)
    {
        $wholeData = [];
        $optionsArray = [];
        foreach ($data['option']['unit_id'] as $option => $value) {
            $optionsArray[] = $option;
        }
        foreach ($optionsArray as $option) {
            if (isset($data['option']['delete'][$option]) && $data['option']['delete'][$option]) {
                $id = $data['option']['delete'][$option];
                $this->unitMappingModel->create()->load($id)->delete();
            } elseif (isset($data['option']['entity_id'][$option]) && $data['option']['entity_id'][$option]) {
                $id = $data['option']['entity_id'][$option];
                $model = $this->unitMappingModel->create()->load($id);
                $model->setUnitId($data['option']['unit_id'][$option]);
                $model->setQty($data['option']['qty'][$option]);
                $model->setQtyPrice($data['option']['price_per_qty'][$option]);
                $model->save();
            } else {
                $unitMappingData = [];
                $unitMappingData['rule_id'] = $ruleId;
                $unitMappingData['unit_id'] = $data['option']['unit_id'][$option];
                $unitMappingData['qty'] = $data['option']['qty'][$option];
                $unitMappingData['qty_price'] = $data['option']['price_per_qty'][$option];
                $wholeData[] = $unitMappingData;
            }
        }
        if (!empty($wholeData)) {
            try {
                $this->connection->beginTransaction();
                $this->connection->insertMultiple($this->resource->getTableName(self::TABLE_NAME), $wholeData);
                $this->connection->commit();
            } catch (\Exception $e) {
                $this->connection->rollBack();
            }
        }
        return true;
    }
}
