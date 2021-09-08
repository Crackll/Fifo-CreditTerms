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

namespace Webkul\MpWholesale\Controller\Adminhtml\Unit;

use Webkul\MpWholesale\Controller\Adminhtml\Unit as UnitController;
use Magento\Backend\App\Action;
use Magento\Framework\Api\DataObjectHelper;
use Webkul\MpWholesale\Api\WholeSalerUnitRepositoryInterface;
use Webkul\MpWholesale\Api\Data\WholeSalerUnitInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;

class Save extends UnitController
{
    /**
     * @var Webkul\MpWholesale\Model\WholeSalerUnitFactory
     */
    protected $wholeSalerUnitFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var WholeSalerUnitRepositoryInterface;
     */
    protected $wholeSalerUnitRepository;

    /**
     * @var WholeSalerUnitInterfaceFactory;
     */
    protected $wholeSalerUnitInterface;

    /**
     * @param Action\Context $context
     * @param DataObjectHelper $dataObjectHelper
     * @param WholeSalerUnitRepositoryInterface $wholeSalerUnitRepository
     * @param WholeSalerUnitInterfaceFactory $wholeSalerUnitInterface
     * @param \Magento\Backend\Model\Auth\Session $authSession
     */
    public function __construct(
        Action\Context $context,
        DataObjectHelper $dataObjectHelper,
        WholeSalerUnitRepositoryInterface $wholeSalerUnitRepository,
        WholeSalerUnitInterfaceFactory $wholeSalerUnitInterface,
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->wholeSalerUnitRepository = $wholeSalerUnitRepository;
        $this->wholeSalerUnitInterface = $wholeSalerUnitInterface;
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
        $wholeData = $this->getRequest()->getPostValue();
        $data = isset($wholeData['unit_form']) ? $wholeData['unit_form'] : false;
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $userId = $this->getCurrentUser()->getUserId();
            $data['user_id'] = $userId;
            $dataObjectRecordDetail = $this->wholeSalerUnitInterface->create();
            $this->dataObjectHelper->populateWithArray(
                $dataObjectRecordDetail,
                $data,
                \Webkul\MpWholesale\Api\Data\WholeSalerUnitInterface::class
            );
            $this->saveDataUnitDetail($dataObjectRecordDetail);
            if (isset($data['entity_id'])) {
                $this->messageManager->addSuccess(__('Unit Details Successfully Updated'));
            } else {
                $this->messageManager->addSuccess(__('Unit Details Successfully Saved'));
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

     /**
      * Save Unit data
      */
    private function saveDataUnitDetail($completeDataObject)
    {
        try {
            $this->wholeSalerUnitRepository->save($completeDataObject);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    $e->getMessage()
                )
            );
        }
    }
}
