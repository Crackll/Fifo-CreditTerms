<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWalletSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWalletSystem\Controller\Adminhtml\Creditrules;

use Webkul\MpWalletSystem\Controller\Adminhtml\Creditrules as CreditrulesController;
use Magento\Backend\App\Action;
use Webkul\MpWalletSystem;

/**
 * Webkul MpWalletSystem Controller
 */
class Delete extends CreditrulesController
{
    /**
     * @var Webkul\MpWalletSystem\Api\WalletCreditRepositoryInterface
     */
    private $creditRuleRepository;

    /**
     * Initialize dependencies
     *
     * @param Action\Context                                     $context
     * @param MpWalletSystem\Api\WalletCreditRepositoryInterface $creditRuleRepository
     */
    public function __construct(
        Action\Context $context,
        MpWalletSystem\Api\WalletCreditRepositoryInterface $creditRuleRepository
    ) {
        parent::__construct($context);
        $this->creditRuleRepository = $creditRuleRepository;
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        /**
         * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
         */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data && array_key_exists('entity_id', $data)) {
            $entityId = $data['entity_id'];
            if ($entityId) {
                try {
                    $this->creditRuleRepository->deleteById($entityId);
                    $this->messageManager->addSuccess(
                        __('Credit Rule successfully deleted.')
                    );
                    return $resultRedirect->setPath('*/*/creditrules');
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException(
                        $e,
                        __('Something went wrong while Deleting the data.')
                    );
                }
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
