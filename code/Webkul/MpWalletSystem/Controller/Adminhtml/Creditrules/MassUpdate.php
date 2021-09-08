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
class MassUpdate extends CreditrulesController
{
    /**
     * @var Webkul\MpWalletSystem\Api\WalletCreditRepositoryInterface
     */
    protected $creditRuleRepository;
    
    /**
     * @var Filter
     */
    protected $filter;
    
    /**
     * @var MpWalletSystem\Model\ResourceModel\Walletcreditrules\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Initialize dependencies
     *
     * @param Action\Context                                                      $context
     * @param Filter                                                              $filter
     * @param Walletsyste\Api\WalletCreditRepositoryInterface                     $creditRuleRepository
     * @param Walletsyste\Model\ResourceModel\Walletcreditrules\CollectionFactory $collectionFactory
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        MpWalletSystem\Api\WalletCreditRepositoryInterface $creditRuleRepository,
        MpWalletSystem\Model\ResourceModel\Walletcreditrules\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->creditRuleRepository = $creditRuleRepository;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Mass Update action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $resultRedirect = $this->resultRedirectFactory->create();
            $data = $this->getRequest()->getParams();
            $status = $data['creditruleupdate'];
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $entityIds = $collection->getAllIds();
            if (!empty($entityIds)) {
                $coditionArr = [];
                foreach ($entityIds as $key => $id) {
                    $condition = "`entity_id`=".$id;
                    array_push($coditionArr, $condition);
                }
                $coditionData = implode(' OR ', $coditionArr);

                $creditRuleCollection = $this->collectionFactory->create();
                $creditRuleCollection->setTableRecords(
                    $coditionData,
                    ['status' => $status]
                );

                $this->messageManager->addSuccess(
                    __(
                        'A Total of %1 record(s) successfully updated.',
                        count($entityIds)
                    )
                );
            }
            return $resultRedirect->setPath('*/*/creditrules');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while Updating the data.')
            );
        }
        return $resultRedirect->setPath('*/*/creditrules');
    }
}
