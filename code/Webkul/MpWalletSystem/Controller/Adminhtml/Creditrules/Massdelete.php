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
use Magento\Ui\Component\MassAction\Filter;

/**
 * Webkul MpWalletSystem Controller
 */
class Massdelete extends CreditrulesController
{
    /**
     * @var Webkul\MpWalletSystem\Api\WalletCreditRepositoryInterface
     */
    private $creditRuleRepository;
    
    /**
     * @var Filter
     */
    private $filter;
    
    /**
     * @var MpWalletSystem\Model\ResourceModel\Walletcreditrules\CollectionFactory
     */
    private $collectionFactory;

    /**
     * Initialize dependencies
     *
     * @param Action\Context                                                         $context
     * @param Filter                                                                 $filter
     * @param MpWalletSystem\Api\WalletCreditRepositoryInterface                     $creditRuleRepository
     * @param MpWalletSystem\Model\ResourceModel\Walletcreditrules\CollectionFactory $collectionFactory
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        MpWalletSystem\Api\WalletCreditRepositoryInterface $creditRuleRepository,
        MpWalletSystem\Model\ResourceModel\Walletcreditrules\CollectionFactory $collectionFactory
    ) {
        $this->creditRuleRepository = $creditRuleRepository;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $creditRuleDeleted = 0;
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        try {
            foreach ($collection as $item) {
                $this->creditRuleRepository->deleteById($item->getEntityId());
                $creditRuleDeleted++;
            }
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) have been deleted.', $creditRuleDeleted)
            );
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
        return $resultRedirect->setPath('*/*/creditrules');
    }
}
