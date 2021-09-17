<?php

/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpRewardSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MpRewardSystem\Controller\Adminhtml\Cart;

use Magento\Backend\App\Action;
use Webkul\MpRewardSystem\Controller\Adminhtml\Cart as CartController;
use Webkul\MpRewardSystem\Model\RewardcartFactory;

class Save extends CartController
{
    /**
     * @var Webkul\MpRewardSystem\Model\RewardcartFactory
     */
    protected $rewardcartFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    /**
     * @param Action\Context                              $context
     * @param RewardcartFactory                           $rewardcartFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        Action\Context $context,
        RewardcartFactory $rewardcartFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->rewardcartFactory = $rewardcartFactory;
        $this->date = $date;
        parent::__construct($context);
    }
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
            $error = $this->validateData($data);
            if (is_array($error) && isset($error[0])) {
                $this->messageManager->addError(__($error[0]));
                return $resultRedirect->setPath('*/*/index');
            }
            $model = $this->rewardcartFactory->create();
            $id = $this->getRequest()->getParam('entity_id');
            if ($id) {
                $duplicate = $this->checkForAlreadyExists($data);
                if ($duplicate) {
                    $this->messageManager->addError(__("Amount range already exist."));
                    return $resultRedirect->setPath('*/*/index');
                }
                $model->load($id);
            } else {
                $duplicate = $this->checkForAlreadyExists($data);
                if ($duplicate) {
                    $this->messageManager->addError(__("Amount range already exist."));
                    return $resultRedirect->setPath('*/*/index');
                }
                $data['created_at'] = $this->date->gmtDate();
            }
            $model->setData($data);
            try {
                $model->save();
                $this->messageManager->addSuccess(
                    __('Cart Rule successfully saved.')
                );
                $this->_session
                    ->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        [
                            'id' => $model->getEntityId(),
                            '_current' => true,
                        ]
                    );
                }
                return $resultRedirect->setPath('*/*/index');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $valueDateTime = strpos($e->getMessage(), "DateTime");
                if ($valueDateTime >= 0) {
                    $this->messageManager->addException(
                        $e,
                        __("Date format is not correct!")
                    );
                } else {
                    $this->messageManager->addException(
                        $e,
                        __('Something went wrong while saving the data.')
                    );
                }
            }
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath(
                '*/*/edit',
                ['id' => $this->getRequest()->getParam('entity_id')]
            );
        }
        return $resultRedirect->setPath('*/*/index');
    }
    /**
     * check already existed cart rule for save
     *
     * @param [] $data
     * @return true|false
     */
    public function checkForAlreadyExists($data)
    {
        $rewardCartSet1 = $this->rewardcartFactory->create()->getCollection()
            ->addFieldToFilter('amount_from', ['lteq' => $data['amount_from']])
            ->addFieldToFilter('amount_to', ['gteq' => $data['amount_to']])
            ->addFieldToFilter('start_date', ['lteq' => $data['start_date']])
            ->addFieldToFilter('end_date', ['gteq' => $data['end_date']])
            ->addFieldToFilter('seller_id', ['eq' => $data['seller_id']]);
        $rewardCartSet2 = $this->rewardcartFactory->create()->getCollection()
            ->addFieldToFilter('amount_from', ['lteq' => $data['amount_to']])
            ->addFieldToFilter('amount_to', ['gteq' => $data['amount_from']])
            ->addFieldToFilter('start_date', ['lteq' => $data['start_date']])
            ->addFieldToFilter('end_date', ['gteq' => $data['end_date']])
            ->addFieldToFilter('seller_id', ['eq' => $data['seller_id']]);
        if (isset($data['entity_id'])) {
            $rewardCartSet1->addFieldToFilter("entity_id", ["neq" => $data['entity_id']]);
            $rewardCartSet2->addFieldToFilter("entity_id", ["neq" => $data['entity_id']]);
        }

        if ((is_array($rewardCartSet1) && !empty($rewardCartSet1))
            || (is_array($rewardCartSet2) && !empty($rewardCartSet2))) {
            return true;
        }
        return false;
    }
    /**
     * validate the cart rule on date
     *
     * @param [] $data
     * @return array
     */
    public function validateData($data)
    {
        $error = [];
        $startDate = strpos($data['start_date'], '-');
        $endDate = strpos($data['end_date'], '-');
        if ($data['start_date'] > $data['end_date']) {
            $error[] = __("End date can not be lesser then start From date.");
        } elseif ($data['amount_from'] >= $data['amount_to']) {
            $error[] = __("Amount To can not be less then Amount From");
        } elseif ($data['amount_from'] < 0 || $data['amount_to'] < 0) {
            $error[] = __("Amount From or Amount To can not be less then 0");
        } elseif ($startDate == false) {
            $error[] = __("Please enter valid date for start From date.");
        } elseif ($endDate == false) {
            $error[] = __("Please enter valid date for End date.");
        }

        return $error;
    }
}
