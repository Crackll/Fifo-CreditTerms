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

namespace Webkul\MpWholesale\Controller\Adminhtml\Pricerule;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;

/**
 * Webkul MpWholesale Pricerule Edit Controller.
 */
class Edit extends \Webkul\MpWholesale\Controller\Adminhtml\Pricerule
{
    /**
     * MpWholesale Pricerule Edit action.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $isUnitExists = $this->checkUnitExists();
        if (!$isUnitExists) {
            $this->messageManager->addNotice(
                __('Please create a Unit first, to create Price Rule.')
            );
            $resultRedirect->setPath('mpwholesale/unit/new');
            return $resultRedirect;
        }
        $priceRuleId = $this->initCurrentPriceRule();
        $isExistingPriceRule = (bool)$priceRuleId;
        $model = $this->priceRuleModel->create();
        if ($isExistingPriceRule) {
            try {
                $model->load($priceRuleId);
                $priceRuleData = [];
                $priceRuleData['wholesale_price_rule'] = [];
                $priceRule = null;
                $priceRule = $this->priceRuleRepositoryInterface->getById(
                    $priceRuleId
                );
                $result = $priceRule->getData();
                if (count($result)) {
                    $priceRuleData['wholesale_price_rule'] = $result;
                    $this->_getSession()->setPriceRuleFormData($priceRuleData);
                } else {
                    $this->messageManager->addError(
                        __('Requested price rule doesn\'t exist')
                    );
                    $resultRedirect->setPath('mpwholesale/pricerule/index');
                    return $resultRedirect;
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while editing the pricerule.')
                );
                $resultRedirect->setPath('mpwholesale/pricerule/index');
                return $resultRedirect;
            }
        }
        $this->coreRegistry->register('entity_pricerule', $model);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $content = $resultPage->getLayout()->createBlock(
            \Webkul\MpWholesale\Block\Adminhtml\Pricerule\Edit::class
        );
        $resultPage->addContent($content);
        $resultPage->setActiveMenu('Webkul_MpWholesale::menu');
        $this->prepareDefaultTitle($resultPage);
        if ($isExistingPriceRule) {
            $resultPage->getConfig()->getTitle()->prepend(
                __('Edit Price Rule %1', $model->getRuleName())
            );
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Price Rule'));
        }
        return $resultPage;
    }

    /**
     * Check if Unit exist for admin user
     *
     * @return boolean
     */
    protected function checkUnitExists()
    {
        $userId = $this->authSession->getUser()->getUserId();
        $wholeSalerUnitCollection = $this->wholeSalerUnitFactory->create()->getCollection()
        ->addFieldToFilter('user_id', $userId);

        if ($wholeSalerUnitCollection->getSize()) {
            return true;
        }
        return false;
    }

    /**
     * Attribute Profile initialization
     *
     * @return string attribute profile id
     */
    protected function initCurrentPriceRule()
    {
        $priceRuleId = (int)$this->getRequest()->getParam('id');
        return $priceRuleId;
    }

    /**
     * Prepare Price rule default title
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return void
     */
    protected function prepareDefaultTitle(
        \Magento\Backend\Model\View\Result\Page $resultPage
    ) {
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Price Rule'));
    }
}
